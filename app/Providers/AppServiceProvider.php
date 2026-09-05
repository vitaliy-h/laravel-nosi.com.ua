<?php

namespace App\Providers;

use App\Modules\Core\Services\LogService;
use App\Modules\Notification\Services\LetterTemplateService;
use App\Modules\Notification\Services\SmsTemplateService;
use App\Modules\Page\Queries\GetAllPagesQuery;
use App\Modules\Translation\Services\TranslationService;
use App\Modules\User\Models\User;
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
        $this->app->singleton(LogService::class);
        // Bound by class name so the dependency is visible at the injection
        // point; string aliases hid it and `new` skipped the container entirely.
        $this->app->singleton(TranslationService::class);
        $this->app->singleton(LetterTemplateService::class);
        $this->app->singleton(SmsTemplateService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // The frontend is the Bootstrap 5 demo theme
        Paginator::useBootstrap();

        // Super Admin bypasses every Gate/Policy check in the app.
        Gate::before(fn ($user, string $ability) => $user->hasRole('super_admin') ? true : null);
        Gate::policy(User::class, UserPolicy::class);

        // Model observers are registered per module by ModuleServiceProvider.

        $this->mergeSocialConfig();
    }

    private function mergeSocialConfig(): void
    {
        foreach (config('social.providers', []) as $provider) {
            config(["services.{$provider}" => config("social.{$provider}")]);
        }

        Event::listen(SocialiteWasCalled::class, TelegramExtendSocialite::class . '@handle');
    }
}
