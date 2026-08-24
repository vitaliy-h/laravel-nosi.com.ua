<?php

namespace App\Modules\Core\Dtos;

use Carbon\CarbonImmutable;
use Spatie\LaravelData\Data;
use Throwable;

/**
 * The admin dashboard period filter, resolved to concrete date boundaries.
 *
 * The only place where the raw filter payload of the dashboard page is turned
 * into dates: widgets and queries never look at `$pageFilters` themselves.
 * Alongside the selected window it carries the previous window of the same
 * length, used by the stat cards to render the percentage change.
 */
class DashboardPeriodData extends Data
{
    public const DEFAULT_KEY = '30';

    public function __construct(
        public readonly CarbonImmutable $from,
        public readonly CarbonImmutable $to,
        public readonly CarbonImmutable $previousFrom,
        public readonly CarbonImmutable $previousTo,
        /** 'today' | '7' | '30' | '90' | 'custom' */
        public readonly string $key,
    ) {}

    /**
     * Build the period from the dashboard page filters. Never throws: a broken
     * value in the URL or session must not take the whole dashboard down, it
     * falls back to the default window instead.
     *
     * @param  array<string, mixed>|null  $filters
     */
    public static function fromFilters(?array $filters): self
    {
        $key = $filters['period'] ?? null;
        $key = is_string($key) || is_int($key) ? (string) $key : '';

        [$from, $to] = match ($key) {
            'today' => [CarbonImmutable::now()->startOfDay(), CarbonImmutable::now()->endOfDay()],
            '7', '30', '90' => [
                CarbonImmutable::now()->subDays((int) $key - 1)->startOfDay(),
                CarbonImmutable::now()->endOfDay(),
            ],
            'custom' => self::customRange($filters),
            default => self::defaultRange(),
        };

        return self::between($from, $to, in_array($key, ['today', '7', '30', '90', 'custom'], true) ? $key : self::DEFAULT_KEY);
    }

    public static function default(): self
    {
        [$from, $to] = self::defaultRange();

        return self::between($from, $to, self::DEFAULT_KEY);
    }

    /**
     * Number of whole days covered by the window, at least one.
     */
    public function days(): int
    {
        $days = (int) $this->from->startOfDay()->diffInDays($this->to->startOfDay());

        return max(1, $days + 1);
    }

    /**
     * Flat cache key part: the driver is `database`, tags are unavailable, so
     * the period has to be spelled out inside the key itself.
     */
    public function cacheKey(): string
    {
        return $this->key.':'.$this->from->toDateString().':'.$this->to->toDateString();
    }

    /**
     * Group the sales chart by hour for a single day, by day otherwise.
     */
    public function isSingleDay(): bool
    {
        return $this->days() === 1;
    }

    private static function between(CarbonImmutable $from, CarbonImmutable $to, string $key): self
    {
        $period = new self($from, $to, $from, $to, $key);
        $days = $period->days();

        return new self(
            from: $from,
            to: $to,
            // The previous window ends right before this one starts and spans
            // the same number of whole days.
            previousFrom: $from->subDays($days),
            previousTo: $from->subSecond(),
            key: $key,
        );
    }

    /**
     * @return array{0: CarbonImmutable, 1: CarbonImmutable}
     */
    private static function defaultRange(): array
    {
        return [
            CarbonImmutable::now()->subDays((int) self::DEFAULT_KEY - 1)->startOfDay(),
            CarbonImmutable::now()->endOfDay(),
        ];
    }

    /**
     * @param  array<string, mixed>|null  $filters
     * @return array{0: CarbonImmutable, 1: CarbonImmutable}
     */
    private static function customRange(?array $filters): array
    {
        $from = self::parseDate($filters['from'] ?? null) ?? CarbonImmutable::now()->subDays(29);
        $to = self::parseDate($filters['to'] ?? null) ?? CarbonImmutable::now();

        // A reversed range comes from a mistyped date, not from an attack:
        // swap the bounds instead of blowing up the page.
        if ($from->greaterThan($to)) {
            [$from, $to] = [$to, $from];
        }

        return [$from->startOfDay(), $to->endOfDay()];
    }

    private static function parseDate(mixed $value): ?CarbonImmutable
    {
        if (blank($value) || (! is_string($value) && ! $value instanceof \DateTimeInterface)) {
            return null;
        }

        try {
            return CarbonImmutable::parse($value);
        } catch (Throwable) {
            return null;
        }
    }
}
