<?php

namespace Tests\Feature\Filament\Admin;

use App\Filament\Resources\LogSms\LogSmsResource;
use App\Filament\Resources\Shop\Brands\BrandResource;
use App\Filament\Resources\Shop\ProductReviews\ProductReviewResource;
use App\Filament\Resources\Shop\Promocodes\PromocodeResource;
use App\Filament\Resources\SmsTemplates\SmsTemplateResource;
use App\Filament\Widgets\Shop\ProductReviewStatsWidget;
use App\Modules\Core\Models\User;
use App\Modules\Core\Support\ModuleGate;
use App\Providers\Filament\AdminPanelProvider;
use Filament\Panel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Disabling a module in config/modules.php must remove it from the admin
 * sidebar and from the panel's resource list, so it can be reached neither by
 * clicking nor by typing its URL.
 */
class AdminNavigationModuleGateTest extends TestCase
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
     * Same reason as in DashboardModuleGateTest: the provider builds its
     * navigation and resource lists once at boot, so the gating logic has to
     * be re-run by hand after overriding config.
     *
     * Overrides can only switch modules OFF here. The panel's routes were
     * registered at boot from the shipped config, and every navigation item
     * resolves its URL through Resource::getUrl() — turning a module on that
     * the booted app never registered would just throw RouteNotFound. Tests
     * that need a module to be on say so with requireModules() instead.
     *
     * @param  array<string, false>  $modules
     */
    private function panelWith(array $modules): Panel
    {
        foreach ($modules as $module => $enabled) {
            config(["modules.{$module}" => $enabled]);
        }

        return (new AdminPanelProvider($this->app))->panel(Panel::make());
    }

    /**
     * Skip unless the given modules are on in the shipped config/modules.php —
     * their navigation items only exist to be asserted on when they are.
     */
    private function requireModules(string ...$modules): void
    {
        foreach ($modules as $module) {
            if (! ModuleGate::isEnabled($module)) {
                $this->markTestSkipped("Module [{$module}] is disabled in config/modules.php.");
            }
        }
    }

    /**
     * Every navigation label in the sidebar, groups flattened into one list.
     *
     * @return array<string>
     */
    private function navigationLabels(Panel $panel): array
    {
        $labels = [];

        foreach ($panel->buildNavigation() as $group) {
            foreach ($group->getItems() as $item) {
                $labels[] = $item->getLabel();
            }
        }

        return $labels;
    }

    public function test_shop_brands_disabled_hides_brands_from_navigation_and_resources(): void
    {
        $this->requireModules('shop', 'shop_brands');

        $panel = $this->panelWith(['shop_brands' => false]);

        $this->assertNotContains(__('admin.tables.shop_brands'), $this->navigationLabels($panel));
        $this->assertNotContains(BrandResource::class, $panel->getResources());

        // The rest of the catalog group survives.
        $this->assertContains(__('admin.tables.shop_products'), $this->navigationLabels($panel));
        $this->assertContains(__('admin.tables.shop_categories'), $this->navigationLabels($panel));
    }

    public function test_shop_reviews_disabled_hides_reviews_from_navigation_and_resources(): void
    {
        $this->requireModules('shop', 'shop_reviews');

        $panel = $this->panelWith(['shop_reviews' => false]);

        $this->assertNotContains(__('admin.tables.shop_product_reviews'), $this->navigationLabels($panel));
        $this->assertNotContains(ProductReviewResource::class, $panel->getResources());
        $this->assertNotContains(ProductReviewStatsWidget::class, $panel->getWidgets());

        // The rest of the catalog group survives.
        $this->assertContains(__('admin.tables.shop_products'), $this->navigationLabels($panel));
    }

    public function test_shop_promocodes_disabled_hides_promocodes_from_navigation_and_resources(): void
    {
        $this->requireModules('shop_cart', 'shop_promocodes');

        $panel = $this->panelWith(['shop_promocodes' => false]);

        $this->assertNotContains(__('admin.tables.shop_promocodes'), $this->navigationLabels($panel));
        $this->assertNotContains(PromocodeResource::class, $panel->getResources());

        // Orders and carts only depend on shop_cart, which is still enabled.
        $this->assertContains(__('admin.tables.shop_orders'), $this->navigationLabels($panel));
        $this->assertContains(__('admin.tables.shop_carts'), $this->navigationLabels($panel));
    }

    public function test_sms_disabled_hides_templates_and_log_from_navigation_and_resources(): void
    {
        $this->requireModules('sms');

        $panel = $this->panelWith(['sms' => false]);

        $labels = $this->navigationLabels($panel);

        $this->assertNotContains(__('admin.tables.sms_templates'), $labels);
        $this->assertNotContains(__('admin.tables.log_sms'), $labels);
        $this->assertNotContains(SmsTemplateResource::class, $panel->getResources());
        $this->assertNotContains(LogSmsResource::class, $panel->getResources());

        // Mail is part of the always-on Notification module.
        $this->assertContains(__('admin.tables.letter_templates'), $labels);
        $this->assertContains(__('admin.tables.log_letters'), $labels);
    }

    public function test_shop_disabled_also_hides_its_dependent_brands_and_promocodes(): void
    {
        $this->requireModules('shop');

        $panel = $this->panelWith(['shop' => false]);

        $labels = $this->navigationLabels($panel);

        $this->assertNotContains(__('admin.tables.shop_brands'), $labels);
        $this->assertNotContains(__('admin.tables.shop_product_reviews'), $labels);
        $this->assertNotContains(__('admin.tables.shop_promocodes'), $labels);
        $this->assertNotContains(BrandResource::class, $panel->getResources());
        $this->assertNotContains(ProductReviewResource::class, $panel->getResources());
        $this->assertNotContains(PromocodeResource::class, $panel->getResources());
    }

    public function test_every_module_enabled_shows_every_gated_item(): void
    {
        $this->requireModules('shop', 'shop_cart', 'shop_brands', 'shop_reviews', 'shop_promocodes', 'sms');

        $panel = $this->panelWith([]);

        $labels = $this->navigationLabels($panel);

        foreach ([
            __('admin.tables.shop_brands'),
            __('admin.tables.shop_product_reviews'),
            __('admin.tables.shop_promocodes'),
            __('admin.tables.sms_templates'),
            __('admin.tables.log_sms'),
        ] as $label) {
            $this->assertContains($label, $labels);
        }

        foreach ([
            BrandResource::class,
            ProductReviewResource::class,
            PromocodeResource::class,
            SmsTemplateResource::class,
            LogSmsResource::class,
        ] as $resource) {
            $this->assertContains($resource, $panel->getResources());
        }
    }
}
