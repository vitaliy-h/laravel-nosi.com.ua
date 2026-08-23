<?php

namespace App\Providers;

use App\Modules\Core\Models\Page;
use App\Modules\Core\Models\User;
use App\Modules\Core\Observers\PageObserver;
use App\Modules\Core\Queries\GetAllPagesQuery;
use App\Modules\Core\Services\UrlHelper;
use App\Modules\Notification\Services\LetterTemplateService;
use App\Modules\Notification\Services\SmsTemplateService;
use App\Modules\Shop\Models\Review;
use App\Modules\Shop\Observers\ReviewObserver;
use App\Modules\ShopCart\Models\Cart;
use App\Modules\ShopCart\Models\CartProduct;
use App\Modules\ShopCart\Observers\CartObserver;
use App\Modules\ShopCart\Observers\CartProductObserver;
use App\Modules\ShopOrders\Models\Order;
use App\Modules\ShopOrders\Models\OrderProduct;
use App\Modules\ShopOrders\Observers\OrderObserver;
use App\Modules\ShopOrders\Observers\OrderProductObserver;
use App\Modules\Translation\Services\TranslationService;
use App\Policies\UserPolicy;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use SocialiteProviders\Manager\SocialiteWasCalled;
use SocialiteProviders\Telegram\TelegramExtendSocialite;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(GetAllPagesQuery::class);
        $this->app->singleton('url-helper', UrlHelper::class);
        $this->app->singleton('translation', function () {
            return app(TranslationService::class);
        });
        $this->app->singleton('letter-template-service', function () {
            return new LetterTemplateService;
        });
        $this->app->singleton('sms-template-service', function () {
            return new SmsTemplateService;
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrap();

        // Super Admin bypasses every Gate/Policy check in the app.
        Gate::before(fn ($user, string $ability) => $user->hasRole('super_admin') ? true : null);
        Gate::policy(User::class, UserPolicy::class);

        Page::observe(PageObserver::class);
        Order::observe(OrderObserver::class);
        OrderProduct::observe(OrderProductObserver::class);
        Cart::observe(CartObserver::class);
        CartProduct::observe(CartProductObserver::class);
        Review::observe(ReviewObserver::class);
        $this->mergeSocialConfig();
    }

    private function mergeSocialConfig(): void
    {
        foreach (config('social.providers', []) as $provider) {
            config(["services.{$provider}" => config("social.{$provider}")]);
        }

        Event::listen(SocialiteWasCalled::class, TelegramExtendSocialite::class.'@handle');
    }
}
