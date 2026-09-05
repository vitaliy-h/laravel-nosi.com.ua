<?php

namespace App\Modules\Core\Console\Commands;

use App\Modules\Core\Models\AuthLog;
use App\Modules\Core\Models\Log;
use App\Modules\Notification\Models\LogLetter;
use App\Modules\Notification\Models\LogSms;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;

#[Signature('logs:prune {--dry-run : Only report what would be deleted}')]
#[Description('Delete journal records older than the configured retention period')]
class PruneLogsCommand extends Command
{
    /** @var array<int, class-string<Model>> */
    private const JOURNALS = [
        Log::class,
        AuthLog::class,
        LogLetter::class,
        LogSms::class,
    ];

    public function handle(): int
    {
        $days = (int) config('log.retention_days', 365);

        if ($days <= 0) {
            $this->warn('log.retention_days is not set — nothing pruned.');

            return self::SUCCESS;
        }

        $threshold = now()->subDays($days);
        $dryRun = (bool) $this->option('dry-run');

        $this->info("Pruning journal records created before {$threshold->toDateTimeString()}");

        foreach (self::JOURNALS as $model) {

            $table = (new $model)->getTable();

            $query = $model::query()->where('created_at', '<', $threshold);

            if ($dryRun) {
                $this->line("{$table}: {$query->count()} records would be deleted");

                continue;
            }

            $deleted = 0;

            // chunked delete: journals can hold millions of rows
            do {
                $chunk = $model::query()
                    ->where('created_at', '<', $threshold)
                    ->limit(1000)
                    ->delete();

                $deleted += $chunk;
            } while ($chunk > 0);

            $this->line("{$table}: {$deleted} records deleted");
        }

        return self::SUCCESS;
    }
}
