<?php

namespace Tests\Feature\Filament\Dashboard;

use App\Filament\Pages\Dashboard;
use App\Modules\Core\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DashboardPageTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = $this->actingAsAdmin();
        $this->actingAs($this->admin);
    }

    public function test_admin_dashboard_returns_ok(): void
    {
        $response = $this->get('/admin');

        $response->assertStatus(200);
    }

    public function test_dashboard_page_mounts_with_the_period_filter(): void
    {
        Livewire::test(Dashboard::class)
            ->assertSuccessful()
            ->assertSet('filters.period', '30');
    }

    public function test_changing_the_period_does_not_break_the_page(): void
    {
        Livewire::test(Dashboard::class)
            ->set('filters.period', 'today')
            ->assertSuccessful()
            ->set('filters.period', '90')
            ->assertSuccessful();
    }

    public function test_custom_range_does_not_break_the_page(): void
    {
        Livewire::test(Dashboard::class)
            ->set('filters.period', 'custom')
            ->set('filters.from', now()->subDays(10)->toDateString())
            ->set('filters.to', now()->toDateString())
            ->assertSuccessful();
    }
}
