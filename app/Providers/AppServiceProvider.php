<?php

namespace App\Providers;

use App\Modules\Core\Models\Page;
use App\Modules\Core\Observers\PageObserver;
use App\Modules\Core\Repositories\PagesRepository;
use App\Modules\Core\Services\LetterTemplateService;
use App\Modules\Core\Services\UrlHelper;
use App\Modules\Shop\Models\Order;
use App\Modules\Shop\Models\OrderProduct;
use App\Modules\Shop\Observers\OrderObserver;
use App\Modules\Shop\Observers\OrderProductObserver;
use App\Modules\Translation\Services\TranslationService;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

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
    }

}
