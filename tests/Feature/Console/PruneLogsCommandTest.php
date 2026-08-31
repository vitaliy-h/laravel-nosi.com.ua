<?php

namespace Tests\Feature\Console;

use App\Modules\Core\Models\AuthLog;
use App\Modules\Core\Models\Log;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PruneLogsCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_records_older_than_the_retention_period_are_deleted(): void
    {
        $old = AuthLog::factory()->create(['created_at' => now()->subDays(400)]);
        $fresh = AuthLog::factory()->create(['created_at' => now()->subDays(10)]);

        $this->artisan('logs:prune')->assertSuccessful();

        $this->assertModelMissing($old);
        $this->assertModelExists($fresh);
    }

    public function test_the_change_journal_is_pruned_too(): void
    {
        $old = Log::factory()->create(['created_at' => now()->subDays(400)]);
        $fresh = Log::factory()->create(['created_at' => now()->subDays(10)]);

        $this->artisan('logs:prune')->assertSuccessful();

        $this->assertModelMissing($old);
        $this->assertModelExists($fresh);
    }

    public function test_dry_run_deletes_nothing(): void
    {
        $old = AuthLog::factory()->create(['created_at' => now()->subDays(400)]);

        $this->artisan('logs:prune', ['--dry-run' => true])->assertSuccessful();

        $this->assertModelExists($old);
    }

    public function test_retention_period_is_configurable(): void
    {
        config(['log.retention_days' => 5]);

        $old = AuthLog::factory()->create(['created_at' => now()->subDays(10)]);
        $fresh = AuthLog::factory()->create(['created_at' => now()->subDay()]);

        $this->artisan('logs:prune')->assertSuccessful();

        $this->assertModelMissing($old);
        $this->assertModelExists($fresh);
    }
}
