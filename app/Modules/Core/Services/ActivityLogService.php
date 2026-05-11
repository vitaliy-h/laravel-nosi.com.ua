<?php

namespace App\Modules\Core\Services;

use App\Modules\Core\Enums\ActivityLogEventEnum;
use App\Modules\Core\Models\ActivityLog;
use App\Modules\Core\Support\ActivityLogSourceResolver;
use Illuminate\Database\Eloquent\Model;

class ActivityLogService
{
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
            event: ActivityLogEventEnum::CREATED,
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
            if($this->onlyTrackChanges($field)) {
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
            event: ActivityLogEventEnum::UPDATED,
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
            event: ActivityLogEventEnum::DELETED,
            model: $model,
            oldValues: $attributes,
            newValues: null,
            changedFields: array_keys($attributes),
        );
    }

    protected function createLog(ActivityLogEventEnum $event, Model $model, ?array $oldValues, ?array $newValues, array $changedFields): void
    {
        ActivityLog::query()->create([
            'user_id' => auth()->id(),

            'event' => $event,

            'model_type' => $model::class,
            'model_id' => $model->getKey(),

            'old_values' => $oldValues,
            'new_values' => $newValues,

            'changed_fields' => $changedFields,

            'source' => ActivityLogSourceResolver::resolve(),

            'ip_address' => request()?->ip(),

        ]);
    }

    protected function shouldLog(Model $model): bool
    {
        if (!config('activity-log.enabled')) {
            return false;
        }

        if (in_array($model::class, config('activity-log.excluded_models'))) {
            return false;
        }

        if (in_array($model->getTable(), config('activity-log.excluded_tables'))) {
            return false;
        }

        return true;
    }

    protected function normalizeAttributes(
        array $attributes,
        Model $model,
    ): array
    {
        $ignored = $this->getIgnoredAttributes($model);

        return collect($attributes)
            ->except($ignored)
            ->map(fn($value, $key) => $this->onlyTrackChanges($key) ? null : $this->normalizeValue($value))
            ->filter(fn($value) => $value !== null)
            ->toArray();
    }

    protected function getIgnoredAttributes(Model $model): array
    {
        $defaultIgnored = config('activity-log.ignored_attributes', []);

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
                ->map(fn($item) => $this->normalizeValue($item))
                ->toArray();
        }

        if (is_object($value)) {
            return method_exists($value, 'toArray')
                ? $value->toArray()
                : (string)$value;
        }

        return $value;
    }

    public function onlyTrackChanges(string $field): bool
    {
        $rules = config('activity-log.only_track_changes', []);

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
