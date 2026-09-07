<?php

namespace App\Modules\Core\Services;

use App\Modules\Core\Enums\LogEventEnum;
use App\Modules\Core\Models\Log;
use App\Modules\Core\Support\LogSourceResolver;
use App\Modules\Core\Support\RequestContext;
use Illuminate\Database\Eloquent\Model;

class LogService
{
    /**
     * Rows collected while a batch is open, instead of being inserted one by one.
     * `null` means no batch is running and every entry is written immediately.
     *
     * @var array<int, array<string, mixed>>|null
     */
    private ?array $buffer = null;

    /**
     * Run $callback with journal writes buffered, then insert them in one query.
     *
     * A cascading delete produces one journal row per child; without this each of
     * them is a separate INSERT.
     *
     * Nested calls join the outer batch — only the outermost one flushes. The
     * flush sits in a `finally`, so a failure half-way still records what already
     * happened.
     */
    public function batch(callable $callback): mixed
    {
        if ($this->buffer !== null) {
            return $callback();
        }

        $this->buffer = [];

        try {
            return $callback();
        } finally {
            $rows = $this->buffer;

            $this->buffer = null;

            if ($rows !== []) {
                Log::query()->insert($rows);
            }
        }
    }

    public function logCreated(Model $model): void
    {
        if (!$this->shouldLog($model)) {
            return;
        }

        $attributes = $this->normalizeAttributes(
            $model->getAttributes(),
            $model
        );

        if (empty($attributes)) {
            return;
        }

        $this->createLog(
            event: LogEventEnum::CREATED,
            model: $model,
            oldValues: null,
            newValues: $attributes,
            changedFields: array_keys($attributes),
        );
    }

    public function logUpdated(Model $model): void
    {
        if (!$this->shouldLog($model)) {
            return;
        }

        $changes = $this->normalizeAttributes(
            $model->getChanges(),
            $model
        );

        if (empty($changes)) {
            return;
        }

        $oldValues = [];
        $newValues = [];

        foreach ($changes as $field => $value) {
            if ($this->onlyTrackChanges($field)) {
                $oldValues[$field] = null;
                $newValues[$field] = null;

                continue;
            }
            $oldValues[$field] = $this->normalizeValue(
                $model->getOriginal($field)
            );

            $newValues[$field] = $this->normalizeValue($value);
        }

        $this->createLog(
            event: LogEventEnum::UPDATED,
            model: $model,
            oldValues: $oldValues,
            newValues: $newValues,
            changedFields: array_keys($changes),
        );
    }

    public function logDeleted(Model $model): void
    {
        if (!$this->shouldLog($model)) {
            return;
        }

        $attributes = $this->normalizeAttributes(
            $model->getOriginal(),
            $model
        );

        if (empty($attributes)) {
            return;
        }

        $this->createLog(
            event: LogEventEnum::DELETED,
            model: $model,
            oldValues: $attributes,
            newValues: null,
            changedFields: array_keys($attributes),
        );
    }

    protected function createLog(LogEventEnum $event, Model $model, ?array $oldValues, ?array $newValues, array $changedFields): void
    {
        $attributes = [
            'user_id' => RequestContext::userId(),

            'event' => $event,

            'model_type' => $model::class,
            'model_id'   => $model->getKey(),

            'old_values' => $oldValues,
            'new_values' => $newValues,

            'changed_fields' => $changedFields,

            'source' => LogSourceResolver::resolve(),

            'ip_address' => RequestContext::ip(),
        ];

        if ($this->buffer === null) {
            Log::query()->create($attributes);

            return;
        }

        // Model casts do not run on a bulk insert, so encode by hand here.
        $now = now();

        $this->buffer[] = [
            ...$attributes,
            'event'          => $event->value,
            'source'         => $attributes['source']?->value,
            'old_values'     => $oldValues === null ? null : json_encode($oldValues),
            'new_values'     => $newValues === null ? null : json_encode($newValues),
            'changed_fields' => json_encode($changedFields),
            'created_at'     => $now,
            'updated_at'     => $now,
        ];
    }

    protected function shouldLog(Model $model): bool
    {
        if (!config('log.enabled')) {
            return false;
        }

        if (in_array($model::class, config('log.excluded_models'))) {
            return false;
        }

        if (in_array($model->getTable(), config('log.excluded_tables'))) {
            return false;
        }

        return true;
    }

    protected function normalizeAttributes(
        array $attributes,
        Model $model,
    ): array {
        $ignored = $this->getIgnoredAttributes($model);

        return collect($attributes)
            ->except($ignored)
            ->map(fn ($value, $key) => $this->onlyTrackChanges($key) ? null : $this->normalizeValue($value))
            ->filter(fn ($value) => $value !== null)
            ->toArray();
    }

    protected function getIgnoredAttributes(Model $model): array
    {
        $defaultIgnored = config('log.ignored_attributes', []);

        $modelIgnored = property_exists($model, 'logExcept')
            ? $model->logExcept
            : [];

        return array_unique([
            ...$defaultIgnored,
            ...$modelIgnored,
        ]);
    }

    protected function normalizeValue(mixed $value): mixed
    {
        if ($value instanceof \UnitEnum) {
            return $value->value;
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d H:i:s');
        }

        if (is_bool($value)) {
            return $value;
        }

        if (is_array($value)) {
            return collect($value)
                ->map(fn ($item) => $this->normalizeValue($item))
                ->toArray();
        }

        if (is_object($value)) {
            return method_exists($value, 'toArray')
                ? $value->toArray()
                : (string) $value;
        }

        return $value;
    }

    public function onlyTrackChanges(string $field): bool
    {
        $rules = config('log.only_track_changes', []);

        foreach ($rules as $rule) {

            if (str_ends_with($rule, '*')) {

                $prefix = rtrim($rule, '*');

                if (str_starts_with($field, $prefix)) {
                    return true;
                }
            }

            if ($field === $rule) {
                return true;
            }
        }

        return false;
    }
}
