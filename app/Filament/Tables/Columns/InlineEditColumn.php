<?php

namespace App\Filament\Tables\Columns;

use BackedEnum;
use Closure;
use DateTimeInterface;
use Filament\Support\Facades\FilamentAsset;
use Filament\Tables\Columns\Concerns\CanBeValidated;
use Filament\Tables\Columns\Concerns\CanUpdateState;
use Filament\Tables\Columns\Contracts\Editable;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Js;

/**
 * A TextColumn that turns into a field on double-click.
 *
 * The cell keeps the TextColumn rendering (badge, date, numeric, formatStateUsing),
 * so the table stays readable; the field — input, select or a checkbox list — only
 * shows while the cell is being edited. Saving goes through Filament's own
 * `updateTableColumnState()`, so hidden/disabled columns and `rules()` are enforced
 * server-side exactly as for `TextInputColumn`.
 *
 * Unlike Filament's editable columns, the cell is disabled when the resource's
 * `canEdit($record)` says no — the same gate as the edit modal. Form logic
 * (`mutateFormDataBeforeSave`, the `generate_seo` toggle) does not run: only the
 * one attribute is saved, model events still fire.
 */
class InlineEditColumn extends TextColumn implements Editable
{
    use CanBeValidated;
    use CanUpdateState {
        updateState as protected updateColumnState;
    }

    protected string $editType = 'text';

    /**
     * @var array<array-key, string> | Closure | null
     */
    protected array|Closure|null $options = null;

    protected bool|Closure $isMultiple = false;

    protected ?string $editAttribute = null;

    protected function setUp(): void
    {
        parent::setUp();

        // An empty editable cell would be blank and give nothing to double-click.
        $this->placeholder(fn (): ?string => $this->isDisabled() ? null : '—');

        $this->disabled(function (?Model $record, mixed $livewire): bool {
            if (!$record || !method_exists($livewire, 'getResource')) {
                return false;
            }

            return !$livewire::getResource()::canEdit($record);
        });
    }

    /**
     * An editable cell must not run the row action (the edit modal) on click:
     * the first click of a double-click would open it, and the cell would be
     * wrapped in a <button>, which an input cannot live in. A read-only cell
     * keeps the row click.
     */
    public function isClickDisabled(): bool
    {
        return parent::isClickDisabled() || !$this->isDisabled();
    }

    public function editAsNumber(): static
    {
        $this->editType = 'number';

        return $this;
    }

    public function editAsDate(): static
    {
        $this->editType = 'date';

        return $this;
    }

    /**
     * Date and time in one browser field (`datetime-local`), minutes precision,
     * in the app timezone — the same as the form's DateTimePicker.
     */
    public function editAsDateTime(): static
    {
        $this->editType = 'datetime-local';

        return $this;
    }

    /**
     * Edit with a select (or, with `multiple()`, a checkbox list) of these options.
     *
     * @param  array<array-key, string> | Closure | null  $options
     */
    public function options(array|Closure|null $options): static
    {
        $this->options = $options;

        return $this;
    }

    public function multiple(bool|Closure $condition = true): static
    {
        $this->isMultiple = $condition;

        return $this;
    }

    /**
     * The attribute the field edits, when the column displays something else —
     * e.g. `InlineEditColumn::make('category.title')->editAttribute('category_id')`.
     * A `belongsToMany` relationship name is saved with `sync()`.
     */
    public function editAttribute(?string $attribute): static
    {
        $this->editAttribute = $attribute;

        return $this;
    }

    public function getEditType(): string
    {
        if ($this->getOptions() === null) {
            return $this->editType;
        }

        return $this->isMultiple() ? 'checkboxes' : 'select';
    }

    /**
     * @return array<array-key, string> | null
     */
    public function getOptions(): ?array
    {
        $options = $this->evaluate($this->options);

        return $options === null ? null : collect($options)->all();
    }

    public function isMultiple(): bool
    {
        return (bool) $this->evaluate($this->isMultiple);
    }

    /**
     * The raw value the field starts from — never the formatted one the cell shows.
     */
    public function getEditState(): mixed
    {
        $record = $this->getRecord();

        if ($this->editAttribute === null) {
            $state = $this->getState();
        } elseif (($relationship = $this->getEditRelationship($record)) !== null) {
            $state = $record->getRelationValue($this->editAttribute)
                ->pluck($relationship->getRelatedKeyName())
                ->all();
        } else {
            $state = $record->getAttribute($this->editAttribute);
        }

        if ($this->isMultiple()) {
            return array_map('strval', array_values(collect($state ?? [])->all()));
        }

        return match (true) {
            $state instanceof BackedEnum        => (string) $state->value,
            $state instanceof DateTimeInterface => $state->format($this->editType === 'datetime-local' ? 'Y-m-d\TH:i' : 'Y-m-d'),
            $state === null                     => '',
            default                             => (string) $state,
        };
    }

    public function updateState(mixed $state): mixed
    {
        if ($this->isMultiple()) {
            $state = array_values(array_filter((array) $state, filled(...)));
        }

        // Filament's own save turns an empty list into null; a cleared multiselect
        // on a plain json column must store [] instead, so it is saved here too.
        $attribute = $this->editAttribute
            ?? (($this->isMultiple() && !str_contains($this->getName(), '.')) ? $this->getName() : null);

        if ($attribute === null || $this->updateStateUsing !== null) {
            return $this->updateColumnState($state);
        }

        if (!$this->isMultiple() && blank($state)) {
            $state = null;
        }

        $this->callBeforeStateUpdated($state);

        $record = $this->getRecord();

        if (($relationship = $this->getEditRelationship($record)) !== null) {
            $relationship->sync($state);
        } else {
            $record->setAttribute($attribute, $state);
            $record->save();
        }

        $this->callAfterStateUpdated($state);

        return $state;
    }

    protected function getEditRelationship(?Model $record): ?BelongsToMany
    {
        if (!$record || $this->editAttribute === null || !method_exists($record, $this->editAttribute)) {
            return null;
        }

        $relationship = $record->{$this->editAttribute}();

        return $relationship instanceof BelongsToMany ? $relationship : null;
    }

    public function toEmbeddedHtml(): string
    {
        $display = parent::toEmbeddedHtml();

        if ($this->isDisabled()) {
            return $display;
        }

        $type = $this->getEditType();

        $config = Js::from([
            'name'      => $this->getName(),
            'recordKey' => $this->getRecordKey(),
            'type'      => $type,
        ]);

        $src = FilamentAsset::getAlpineComponentSrc('inline-edit-column');

        ob_start(); ?>

        <div
            wire:ignore.self
            x-load
            x-load-src="<?= e($src) ?>"
            x-data="inlineEditColumn(<?= e($config) ?>)"
            x-on:keydown.esc.stop.prevent="cancel()"
            class="fi-inline-edit"
            x-bind:class="{ 'fi-inline-edit-active': editing }"
        >
            <input type="hidden" value="<?= e(json_encode($this->getEditState())) ?>" x-ref="serverState" />

            <div x-show="! editing" x-on:dblclick="open()" class="fi-inline-edit-display">
                <?= $display ?>
            </div>

            <div x-show="editing" x-cloak x-on:click.stop class="fi-inline-edit-field">
                <?= $this->renderField($type) ?>

                <p x-show="error !== undefined" x-text="error" class="fi-inline-edit-error"></p>
            </div>
        </div>

        <?php return ob_get_clean();
    }

    protected function renderField(string $type): string
    {
        $wrapper = 'class="fi-input-wrp" x-bind:class="{ \'fi-disabled\': isLoading, \'fi-invalid\': error !== undefined }"';
        $label = e(strip_tags((string) $this->getLabel()));

        if ($type === 'checkboxes') {
            $items = '';

            foreach ($this->getOptions() as $value => $optionLabel) {
                $items .= '<label class="fi-inline-edit-option">'
                    . '<input type="checkbox" class="fi-checkbox-input" x-model="state" value="' . e($value) . '" />'
                    . '<span>' . e($optionLabel) . '</span>'
                    . '</label>';
            }

            return '<div x-ref="input" tabindex="-1" aria-label="' . $label . '" class="fi-inline-edit-options"'
                . ' x-on:click.outside="editing && save()" x-on:keydown.enter.prevent="save()">'
                . $items
                . '</div>';
        }

        if ($type === 'select') {
            $options = '<option value=""></option>';

            foreach ($this->getOptions() as $value => $optionLabel) {
                $options .= '<option value="' . e($value) . '">' . e($optionLabel) . '</option>';
            }

            return '<div ' . $wrapper . '>'
                . '<select x-ref="input" x-model="state" aria-label="' . $label . '" class="fi-select-input"'
                . ' x-bind:disabled="isLoading" x-on:change="save()" x-on:blur="save()">'
                . $options
                . '</select></div>';
        }

        return '<div ' . $wrapper . '><div class="fi-input-wrp-content-ctn">'
            . '<input x-ref="input" x-model="state" type="' . e($type) . '" aria-label="' . $label . '"'
            . ($type === 'number' ? ' step="any"' : '')
            . ' class="fi-input" x-bind:disabled="isLoading"'
            . ' x-on:keydown.enter.prevent="save()" x-on:blur="save()" />'
            . '</div></div>';
    }
}
