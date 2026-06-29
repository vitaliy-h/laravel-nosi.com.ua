<?php

namespace App\Providers;

use App\Modules\Core\Models\Page;
use App\Modules\Core\Observers\PageObserver;
use App\Modules\Core\Repositories\PagesRepository;
use App\Modules\Notification\Services\LetterTemplateService;
use App\Modules\Notification\Services\SmsTemplateService;
use App\Modules\Core\Services\UrlHelper;
use App\Modules\Shop\Models\Order;
use App\Modules\Shop\Models\OrderProduct;
use App\Modules\Shop\Observers\OrderObserver;
use App\Modules\Shop\Observers\OrderProductObserver;
use App\Modules\Translation\Services\TranslationService;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use SocialiteProviders\Manager\SocialiteWasCalled;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(PagesRepository::class);
        $this->app->singleton('url-helper', function () {
            return new UrlHelper(app(PagesRepository::class));
        });
        $this->app->singleton('translation', function () {
            return app(TranslationService::class);
        });
        $this->app->singleton('letter-template-service', function () {
            return new LetterTemplateService();
        });
        $this->app->singleton('sms-template-service', function () {
            return new SmsTemplateService();
        });
    }


    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrap();
        Page::observe(PageObserver::class);
        Order::observe(OrderObserver::class);
        OrderProduct::observe(OrderProductObserver::class);
        $this->mergeSocialConfig();
    }

    private function mergeSocialConfig(): void
    {
        foreach (config('social.providers', []) as $provider) {
            config(["services.{$provider}" => config("social.{$provider}")]);
        }

        Event::listen(SocialiteWasCalled::class, \SocialiteProviders\Telegram\TelegramExtendSocialite::class . '@handle');
    }

}
