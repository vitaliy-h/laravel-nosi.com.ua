<?php

namespace App\Modules\Core\Dtos;

use Spatie\LaravelData\Data;

/**
 * Shared result envelope for Actions.
 *
 * An Action returns exactly one of:
 *
 * 1. `void` — it mutates state and has nothing to report;
 * 2. a domain value (`Order`, `User`, `float`, …) — it always succeeds, and any
 *    failure is an exception;
 * 3. `ActionResult<T>` — the failure is an expected business outcome the caller
 *    must branch on (invalid promocode, quota exceeded).
 *
 * `bool` is deliberately not on that list: a bare true/false carries no reason
 * for the failure. Use `ActionResult` (with a message) or `void` (nothing to
 * report). A genuine two-state query belongs in a Query class, not an Action.
 *
 * The payload lives in its own small DTO inside the owning module, so its
 * fields stay non-nullable — they only exist on the success branch. The
 * envelope never carries payload fields itself.
 *
 * The `@template` annotations are documentation until a static analyzer is
 * added (see the larastan issue); at runtime `$data` is `mixed`.
 *
 * @template TData
 */
class ActionResult extends Data
{
    /**
     * @param  TData|null  $data
     */
    public function __construct(
        public readonly bool $success,
        public readonly ?string $message = null,
        public readonly mixed $data = null,
    ) {}

    /**
     * @template TOk
     *
     * @param  TOk|null  $data
     * @return self<TOk>
     */
    public static function ok(mixed $data = null, ?string $message = null): self
    {
        return new self(success: true, message: $message, data: $data);
    }

    /**
     * @return self<null>
     */
    public static function fail(string $message): self
    {
        return new self(success: false, message: $message);
    }
}
