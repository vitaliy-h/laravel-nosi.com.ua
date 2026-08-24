<?php

namespace Tests\Feature\Filament\Dashboard;

use App\Filament\Widgets\NeedsAttentionWidget;
use App\Filament\Widgets\Shop\CatalogStatsWidget;
use App\Filament\Widgets\Shop\LatestOrdersWidget;
use App\Filament\Widgets\Shop\PromocodeStatsWidget;
use App\Filament\Widgets\Shop\ProductReviewStatsWidget;
use App\Filament\Widgets\Shop\SalesChartWidget;
use App\Filament\Widgets\Shop\SalesStatsWidget;
use App\Filament\Widgets\Shop\TopProductsWidget;
use App\Filament\Widgets\Users\UserStatsWidget;
use App\Modules\Core\Models\User;
use App\Providers\Filament\AdminPanelProvider;
use Filament\Panel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardModuleGateTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = $this->actingAsAdmin();
        $this->actingAs($this->admin);
    }

    public function test_dashboard_opens_without_errors(): void
    {
        $response = $this->get('/admin');

        $response->assertStatus(200);
    }

    /**
     * The AdminPanelProvider is a service provider: its ->widgets() list is
     * built once when the app boots, before any config() override made
     * inside a test body can take effect. Re-running ->panel() directly,
     * after overriding config, is the only way to exercise the actual gating
     * logic that ships in the provider.
     */
    private function widgetsWith(array $config): array
    {
        config($config);

        $panel = (new AdminPanelProvider($this->app))->panel(Panel::make());

        return $panel->getWidgets();
    }

    public function test_shop_disabled_drops_every_shop_dependent_widget(): void
    {
        $widgets = $this->widgetsWith(['modules.shop' => false]);

        foreach ([
            SalesStatsWidget::class,
            SalesChartWidget::class,
            LatestOrdersWidget::class,
            CatalogStatsWidget::class,
            TopProductsWidget::class,
            PromocodeStatsWidget::class,
            ProductReviewStatsWidget::class,
        ] as $widget) {
            $this->assertNotContains($widget, $widgets, "{$widget} should not be registered when shop is disabled.");
        }

        // Module-independent widgets stay registered.
        $this->assertContains(NeedsAttentionWidget::class, $widgets);
        $this->assertContains(UserStatsWidget::class, $widgets);
    }

    public function test_shop_cart_disabled_drops_only_cart_and_order_widgets(): void
    {
        $widgets = $this->widgetsWith(['modules.shop_cart' => false]);

        foreach ([
            SalesStatsWidget::class,
            SalesChartWidget::class,
            LatestOrdersWidget::class,
            TopProductsWidget::class,
            PromocodeStatsWidget::class,
        ] as $widget) {
            $this->assertNotContains($widget, $widgets, "{$widget} should not be registered when shop_cart is disabled.");
        }

        // Catalog and reviews only depend on `shop`, which is still enabled.
        $this->assertContains(CatalogStatsWidget::class, $widgets);
        $this->assertContains(ProductReviewStatsWidget::class, $widgets);
    }

    public function test_every_module_enabled_registers_every_widget(): void
    {
        $widgets = $this->widgetsWith([]);

        foreach ([
            SalesStatsWidget::class,
            SalesChartWidget::class,
            LatestOrdersWidget::class,
            NeedsAttentionWidget::class,
            CatalogStatsWidget::class,
            TopProductsWidget::class,
            PromocodeStatsWidget::class,
            ProductReviewStatsWidget::class,
            UserStatsWidget::class,
        ] as $widget) {
            $this->assertContains($widget, $widgets);
        }
    }
}
