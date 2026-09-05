<?php

namespace Tests\Feature\Filament\Dashboard;

use App\Filament\Widgets\NeedsAttentionWidget;
use App\Filament\Widgets\Shop\CatalogStatsWidget;
use App\Filament\Widgets\Shop\LatestOrdersWidget;
use App\Filament\Widgets\Shop\ProductReviewStatsWidget;
use App\Filament\Widgets\Shop\PromocodeStatsWidget;
use App\Filament\Widgets\Shop\SalesChartWidget;
use App\Filament\Widgets\Shop\SalesStatsWidget;
use App\Filament\Widgets\Shop\TopProductsWidget;
use App\Filament\Widgets\System\RecentActivityWidget;
use App\Filament\Widgets\System\SystemStatsWidget;
use App\Filament\Widgets\Users\UserStatsWidget;
use App\Modules\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class DashboardWidgetsTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = $this->actingAsAdmin();
        $this->actingAs($this->admin);
    }

    /**
     * @return array<string, array{0: class-string}>
     */
    public static function periodWidgetsProvider(): array
    {
        return [
            'sales stats'     => [SalesStatsWidget::class],
            'sales chart'     => [SalesChartWidget::class],
            'latest orders'   => [LatestOrdersWidget::class],
            'needs attention' => [NeedsAttentionWidget::class],
            'catalog stats'   => [CatalogStatsWidget::class],
            'top products'    => [TopProductsWidget::class],
            'promocode stats' => [PromocodeStatsWidget::class],
            'review stats'    => [ProductReviewStatsWidget::class],
            'user stats'      => [UserStatsWidget::class],
            'system stats'    => [SystemStatsWidget::class],
            'recent activity' => [RecentActivityWidget::class],
        ];
    }

    /**
     * @param  class-string  $widget
     */
    #[DataProvider('periodWidgetsProvider')]
    public function test_widget_mounts_successfully(string $widget): void
    {
        // A widget whose module is off is never put on the dashboard, and it
        // cannot render on its own either: the resources it links to have no
        // routes then. Mounting it here would test a state that cannot happen.
        if (!$widget::canView()) {
            $this->markTestSkipped("Widget [{$widget}] is disabled by config/modules.php.");
        }

        Livewire::test($widget, ['pageFilters' => ['period' => '30']])
            ->assertSuccessful();
    }

    public function test_system_widgets_are_visible_to_every_admin(): void
    {
        $this->assertTrue(SystemStatsWidget::canView());
        $this->assertTrue(RecentActivityWidget::canView());

        $superAdmin = $this->actingAsSuperAdmin();
        $this->actingAs($superAdmin);

        $this->assertTrue(SystemStatsWidget::canView());
        $this->assertTrue(RecentActivityWidget::canView());
    }
}
