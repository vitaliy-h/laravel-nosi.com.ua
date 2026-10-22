<?php

namespace Tests\Feature\Core;

use App\Modules\Core\Services\PostDeployService;
use Mockery\MockInterface;
use Tests\TestCase;

class DeployControllerTest extends TestCase
{
    public function test_endpoint_is_absent_without_a_configured_token(): void
    {
        config(['app.deploy_token' => null]);

        $this->postJson('/api/deploy', [], ['X-Deploy-Token' => ''])->assertNotFound();
    }

    public function test_wrong_token_is_rejected_without_running_anything(): void
    {
        config(['app.deploy_token' => 'secret']);
        $this->mock(PostDeployService::class, fn (MockInterface $mock) => $mock->shouldNotReceive('handle'));

        $this->postJson('/api/deploy', [], ['X-Deploy-Token' => 'wrong'])->assertForbidden();
    }

    public function test_valid_token_runs_the_post_deploy_commands(): void
    {
        config(['app.deploy_token' => 'secret']);
        $this->mock(PostDeployService::class, fn (MockInterface $mock) => $mock->shouldReceive('handle')->once()->andReturn([
            'success' => true,
            'steps'   => [['command' => 'migrate', 'ok' => true, 'output' => '']],
        ]));

        $this->postJson('/api/deploy', [], ['X-Deploy-Token' => 'secret'])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.0.command', 'migrate');
    }

    public function test_a_failed_step_answers_500(): void
    {
        config(['app.deploy_token' => 'secret']);
        $this->mock(PostDeployService::class, fn (MockInterface $mock) => $mock->shouldReceive('handle')->once()->andReturn([
            'success' => false,
            'steps'   => [['command' => 'migrate', 'ok' => false, 'output' => 'SQLSTATE']],
        ]));

        $this->postJson('/api/deploy', [], ['X-Deploy-Token' => 'secret'])
            ->assertStatus(500)
            ->assertJsonPath('success', false);
    }
}
