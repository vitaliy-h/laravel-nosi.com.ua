<?php

namespace App\Providers\Filament;

use App\Modules\Core\Support\ModuleGate;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationBuilder;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Tables\Table;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Request;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        Table::configureUsing(function (Table $table): void {
            $table
                ->recordUrl(fn() => null)//Do not open view page when clicking a table row
                //->striped()
                ->defaultPaginationPageOption(25)
                ->paginationPageOptions([25, 50, 100]);
        });
        return $panel
            ->default()
            ->sidebarWidth('15rem') // default ~16rem
            ->sidebarFullyCollapsibleOnDesktop()
            //->topNavigation() //navigation on top
            ->id('admin')
            ->path('admin') //path to admin panel in browser
            ->login(\App\Filament\Pages\Auth\Login::class)
            ->navigation(function (NavigationBuilder $builder): NavigationBuilder {
                return $builder
                    ->items(array_filter([
                        NavigationItem::make(__('admin.tables.dashboard'))
                            ->icon('heroicon-o-home')
                            ->url(Dashboard::getUrl())
                            ->isActiveWhen(fn(): bool => Request::routeIs('filament.admin.pages.dashboard')),
                        NavigationItem::make(__('admin.tables.pages'))
                            ->icon('heroicon-o-document-text')
                            ->url(\App\Filament\Resources\Pages\PageResource::getUrl())
                            ->isActiveWhen(fn(): bool => Request::routeIs('filament.admin.resources.pages.*')),
                        NavigationItem::make(__('admin.tables.languages'))
                            ->icon('heroicon-o-language')
                            ->url(\App\Filament\Resources\Languages\LanguageResource::getUrl())
                            ->isActiveWhen(fn(): bool => Request::routeIs('filament.admin.resources.languages.*')),
                        ModuleGate::isEnabled('news') ? NavigationItem::make(__('admin.tables.news'))
                            ->icon('heroicon-o-newspaper')
                            ->url(\App\Filament\Resources\News\NewsResource::getUrl())
                            ->isActiveWhen(fn(): bool => Request::routeIs('filament.admin.resources.news.*')) : null,
                        ModuleGate::isEnabled('feedback') ? NavigationItem::make(__('admin.tables.feedback'))
                            ->icon('heroicon-o-chat-bubble-left-right')
                            ->url(\App\Filament\Resources\Feedback\FeedbackResource::getUrl())
                            ->isActiveWhen(fn(): bool => Request::routeIs('filament.admin.resources.feedback.*')) : null,
                        NavigationItem::make(__('admin.tables.letter_templates'))
                            ->icon('heroicon-o-envelope')
                            ->url(\App\Filament\Resources\LetterTemplates\LetterTemplateResource::getUrl())
                            ->isActiveWhen(fn(): bool => Request::routeIs('filament.admin.resources.letter-templates.*')),
                        NavigationItem::make(__('admin.tables.sms_templates'))
                            ->icon('heroicon-o-chat-bubble-left-right')
                            ->url(\App\Filament\Resources\SmsTemplates\SmsTemplateResource::getUrl())
                            ->isActiveWhen(fn(): bool => Request::routeIs('filament.admin.resources.sms-templates.*')),
                        auth()->user()?->isSuperAdmin() ? NavigationItem::make(__('admin.debug_tools.navigation_label'))
                            ->icon('heroicon-o-command-line')
                            ->url(\App\Filament\Pages\DebugTools\DebugTools::getUrl())
                            ->isActiveWhen(fn(): bool => Request::routeIs('filament.admin.pages.debug-tools')) : null,
                    ]))
                    ->groups(array_filter([
                        // catalog
                        ModuleGate::isEnabled('shop') ? NavigationGroup::make(__('admin.tables.catalog'))
                            ->items([
                                NavigationItem::make(__('admin.tables.shop_products'))
                                    ->icon('heroicon-o-cube')
                                    ->url(\App\Filament\Resources\Shop\Products\ProductResource::getUrl())
                                    ->isActiveWhen(fn(): bool => Request::routeIs('filament.admin.resources.shop.products*')),
                                NavigationItem::make(__('admin.tables.shop_categories'))
                                    ->icon('heroicon-o-squares-2x2')
                                    ->url(\App\Filament\Resources\Shop\Categories\CategoryResource::getUrl())
                                    ->isActiveWhen(fn(): bool => Request::routeIs('filament.admin.resources.shop.categories*')),
                                NavigationItem::make(__('admin.tables.shop_brands'))
                                    ->icon('heroicon-o-tag')
                                    ->url(\App\Filament\Resources\Shop\Brands\BrandResource::getUrl())
                                    ->isActiveWhen(fn(): bool => Request::routeIs('filament.admin.resources.shop.brands*')),
                                NavigationItem::make(__('admin.tables.shop_reviews'))
                                    ->icon('heroicon-o-star')
                                    ->url(\App\Filament\Resources\Shop\Reviews\ReviewResource::getUrl())
                                    ->isActiveWhen(fn(): bool => Request::routeIs('filament.admin.resources.shop.reviews*')),
                            ]) : null,
                        // shop
                        ModuleGate::isEnabled('shop_cart') ? NavigationGroup::make(__('admin.tables.shop'))
                            ->items([
                                NavigationItem::make(__('admin.tables.shop_orders'))
                                    ->icon('heroicon-o-shopping-cart')
                                    ->url(\App\Filament\Resources\Shop\Orders\OrderResource::getUrl())
                                    ->isActiveWhen(fn(): bool => Request::routeIs('filament.admin.resources.shop.orders.*')),
                                NavigationItem::make(__('admin.tables.shop_carts'))
                                    ->icon('heroicon-o-shopping-bag')
                                    ->url(\App\Filament\Resources\Shop\Cart\CartResource::getUrl())
                                    ->isActiveWhen(fn(): bool => Request::routeIs('filament.admin.resources.cart.carts.*')),
                            ]) : null,
                        // users
                        NavigationGroup::make(__('admin.tables.users'))
                            ->items(array_filter([
                                NavigationItem::make(__('admin.tables.users'))
                                    ->icon('heroicon-o-users')
                                    ->url(\App\Filament\Resources\Users\UserResource::getUrl())
                                    ->isActiveWhen(fn(): bool => Request::is('admin/users') || Request::is('admin/users/create') || Request::is('admin/users/*/edit')),
                                ModuleGate::isEnabled('auth') ? NavigationItem::make(__('admin.tables.social_profiles'))
                                    ->icon('heroicon-o-link')
                                    ->url(\App\Filament\Resources\SocialProfiles\SocialProfileResource::getUrl())
                                    ->isActiveWhen(fn(): bool => Request::is('admin/social-profiles*')) : null,
                                ModuleGate::isEnabled('subscribers') ? NavigationItem::make(__('admin.tables.subscribers'))
                                    ->icon('heroicon-o-envelope-open')
                                    ->url(\App\Filament\Resources\Subscribers\SubscriberResource::getUrl())
                                    ->isActiveWhen(fn(): bool => Request::routeIs('filament.admin.resources.subscribers.*')) : null,
                            ])),

                        ModuleGate::isEnabled('geo') ? NavigationGroup::make(__('admin.tables.geo'))
                            ->items([
                                NavigationItem::make(__('admin.tables.geo_countries'))
                                    ->icon('heroicon-o-globe-alt')
                                    ->url(\App\Filament\Resources\Geo\GeoCountries\GeoCountryResource::getUrl())
                                    ->isActiveWhen(fn(): bool => Request::routeIs('filament.admin.resources.geo.geo-countries*')),
                                NavigationItem::make(__('admin.tables.geo_regions'))
                                    ->icon('heroicon-o-map')
                                    ->url(\App\Filament\Resources\Geo\GeoRegions\GeoRegionResource::getUrl())
                                    ->isActiveWhen(fn(): bool => Request::routeIs('filament.admin.resources.geo.geo-regions*')),
                                NavigationItem::make(__('admin.tables.geo_cities'))
                                    ->icon('heroicon-o-building-office-2')
                                    ->url(\App\Filament\Resources\Geo\GeoCities\GeoCityResource::getUrl())
                                    ->isActiveWhen(fn(): bool => Request::routeIs('filament.admin.resources.geo.geo-cities*')),
                            ]) : null,

                        NavigationGroup::make(__('admin.tables.logs'))
                            ->items(array_filter([
                                NavigationItem::make(__('admin.tables.logs'))
                                    ->icon('heroicon-o-archive-box')
                                    ->url(\App\Filament\Resources\Logs\LogResource::getUrl())
                                    ->isActiveWhen(fn(): bool => Request::routeIs('filament.admin.resources.logs.*')),
                                NavigationItem::make(__('admin.tables.log_letters'))
                                    ->icon('heroicon-o-inbox')
                                    ->url(\App\Filament\Resources\LogLetters\LogLetterResource::getUrl())
                                    ->isActiveWhen(fn(): bool => Request::routeIs('filament.admin.resources.log-letters.*')),
                                NavigationItem::make(__('admin.tables.log_sms'))
                                    ->icon('heroicon-o-chat-bubble-bottom-center-text')
                                    ->url(\App\Filament\Resources\LogSms\LogSmsResource::getUrl())
                                    ->isActiveWhen(fn(): bool => Request::routeIs('filament.admin.resources.log-sms.*')),
                            ])),
                    ]));
            })
            ->colors([
                'primary' => Color::Sky,
            ])
            ->favicon(asset('favicon.svg'))
            ->resources(array_filter([
                \App\Filament\Resources\Pages\PageResource::class,
                \App\Filament\Resources\Languages\LanguageResource::class,
                \App\Filament\Resources\LetterTemplates\LetterTemplateResource::class,
                \App\Filament\Resources\LogLetters\LogLetterResource::class,
                \App\Filament\Resources\Logs\LogResource::class,
                \App\Filament\Resources\Users\UserResource::class,
                \App\Filament\Resources\SmsTemplates\SmsTemplateResource::class,
                \App\Filament\Resources\LogSms\LogSmsResource::class,
                ModuleGate::isEnabled('news') ? \App\Filament\Resources\News\NewsResource::class : null,
                ModuleGate::isEnabled('feedback') ? \App\Filament\Resources\Feedback\FeedbackResource::class : null,
                ModuleGate::isEnabled('auth') ? \App\Filament\Resources\SocialProfiles\SocialProfileResource::class : null,
                ModuleGate::isEnabled('subscribers') ? \App\Filament\Resources\Subscribers\SubscriberResource::class : null,
                ModuleGate::isEnabled('geo') ? \App\Filament\Resources\Geo\GeoCountries\GeoCountryResource::class : null,
                ModuleGate::isEnabled('geo') ? \App\Filament\Resources\Geo\GeoRegions\GeoRegionResource::class : null,
                ModuleGate::isEnabled('geo') ? \App\Filament\Resources\Geo\GeoCities\GeoCityResource::class : null,
                ModuleGate::isEnabled('shop') ? \App\Filament\Resources\Shop\Products\ProductResource::class : null,
                ModuleGate::isEnabled('shop') ? \App\Filament\Resources\Shop\Categories\CategoryResource::class : null,
                ModuleGate::isEnabled('shop') ? \App\Filament\Resources\Shop\Brands\BrandResource::class : null,
                ModuleGate::isEnabled('shop') ? \App\Filament\Resources\Shop\Reviews\ReviewResource::class : null,
                ModuleGate::isEnabled('shop') ? \App\Filament\Resources\Shop\Orders\OrderResource::class : null,
                ModuleGate::isEnabled('shop_cart') ? \App\Filament\Resources\Shop\Cart\CartResource::class : null,
            ]))
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AccountWidget::class,
                FilamentInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->breadcrumbs(false);
    }
}
