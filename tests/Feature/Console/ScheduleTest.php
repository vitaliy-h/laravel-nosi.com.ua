<?php

namespace Tests\Feature\Console;

use Illuminate\Console\Scheduling\Event;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class ScheduleTest extends TestCase
{
    /**
     * A scheduled command that no longer exists fails silently every night, so
     * the schedule is asserted against the commands Artisan actually resolves.
     */
    public function test_every_scheduled_command_exists(): void
    {
        $registered = array_keys(Artisan::all());

        foreach ($this->scheduledCommandNames() as $name) {
            $this->assertContains($name, $registered, "Scheduled command [{$name}] is not registered.");
        }
    }

    public function test_abandoned_carts_cleanup_is_scheduled(): void
    {
        $this->assertContains('cart:clear-abandoned', $this->scheduledCommandNames());
    }

    public function test_log_pruning_is_scheduled(): void
    {
        $this->assertContains('logs:prune', $this->scheduledCommandNames());
    }

    /**
     * @return array<int, string>
     */
    private function scheduledCommandNames(): array
    {
        // withSchedule() in bootstrap/app.php hangs off Artisan::starting(), so
        // the console kernel has to boot before the Schedule is resolved.
        Artisan::all();

        $names = [];

        foreach (app(Schedule::class)->events() as $event) {
            $names[] = $this->commandNameFrom($event);
        }

        return array_values(array_filter($names));
    }

    private function commandNameFrom(Event $event): ?string
    {
        if (!preg_match("/artisan['\"]? (.+)$/", $event->command ?? '', $matches)) {
            return null;
        }

        $arguments = str_replace("'", '', trim($matches[1]));

        return explode(' ', $arguments)[0];
    }
}
