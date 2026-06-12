<?php

namespace Tests\Feature\Infrastructure;

use App\Modules\Core\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LogObserverSmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_creating_a_model_does_not_throw_due_to_log_observer(): void
    {
        $user = User::factory()->create();

        $this->assertModelExists($user);
    }
}
