<?php

namespace App\Providers\Filament;

use App\Filament\Http\Middleware\SetAdminLocale;
use App\Filament\Pages\Auth\Login;
use App\Filament\Pages\Dashboard;
use App\Filament\Pages\DebugTools\DebugTools;
use App\Filament\Resources\AuthLogs\AuthLogResource;
use App\Filament\Resources\Feedback\FeedbackResource;
use App\Filament\Resources\Geo\GeoCities\GeoCityResource;
use App\Filament\Resources\Geo\GeoCountries\GeoCountryResource;
use App\Filament\Resources\Geo\GeoRegions\GeoRegionResource;
use App\Filament\Resources\Home\Announces\AnnounceResource;
use App\Filament\Resources\Home\Faqs\FaqResource;
use App\Filament\Resources\Home\Promos\PromoResource;
use App\Filament\Resources\Home\Reviews\ReviewResource;
use App\Filament\Resources\Home\Sliders\SliderResource;
use App\Filament\Resources\Languages\LanguageResource;
use App\Filament\Resources\LetterTemplates\LetterTemplateResource;
use App\Filament\Resources\LogLetters\LogLetterResource;
use App\Filament\Resources\Logs\LogResource;
use App\Filament\Resources\LogSms\LogSmsResource;
use App\Filament\Resources\LogTelegram\LogTelegramResource;
use App\Filament\Resources\News\NewsResource;
use App\Filament\Resources\Pages\PageResource;
use App\Filament\Resources\Shop\Attributes\AttributeResource;
use App\Filament\Resources\Shop\Brands\BrandResource;
use App\Filament\Resources\Shop\Cart\CartResource;
use App\Filament\Resources\Shop\Categories\CategoryResource;
use App\Filament\Resources\Shop\Orders\OrderResource;
use App\Filament\Resources\Shop\Products\ProductResource;
use App\Filament\Resources\Shop\Promocodes\PromocodeResource;
use App\Filament\Resources\SmsTemplates\SmsTemplateResource;
use App\Filament\Resources\SocialProfiles\SocialProfileResource;
use App\Filament\Resources\Subscribers\SubscriberResource;
use App\Filament\Resources\Users\UserResource;
use App\Filament\Widgets\NeedsAttentionWidget;
use App\Filament\Widgets\Review\ReviewStatsWidget;
use App\Filament\Widgets\Shop\CatalogStatsWidget;
use App\Filament\Widgets\Shop\LatestOrdersWidget;
use App\Filament\Widgets\Shop\PromocodeStatsWidget;
use App\Filament\Widgets\Shop\SalesChartWidget;
use App\Filament\Widgets\Shop\SalesStatsWidget;
use App\Filament\Widgets\Shop\TopProductsWidget;
use App\Filament\Widgets\System\RecentActivityWidget;
use App\Filament\Widgets\System\SystemStatsWidget;
use App\Filament\Widgets\Users\UserStatsWidget;
use App\Modules\Core\Support\ModuleGate;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationBuilder;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Resources\Pages\Page as ResourcePage;
use Filament\Support\Assets\AlpineComponent;
use Filament\Support\Assets\Css;
use Filament\Support\Colors\Color;
use Filament\Support\Facades\FilamentAsset;
use Filament\Tables\Table;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Request;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function boot(): void
    {
        // Plain files published by `filament:assets` (run on composer's
        // post-autoload-dump via `filament:upgrade`), not built by Vite.
        FilamentAsset::register([
            AlpineComponent::make('inline-edit-column', resource_path('js/filament/inline-edit-column.js')),
            Css::make('inline-edit-column', resource_path('css/filament/inline-edit-column.css')),
        ]);
    }

    public function panel(Panel $panel): Panel
    {
        Table::configureUsing(function (Table $table): void {
            $table
                ->recordUrl(fn () => null)// Do not open view page when clicking a table row
                // ->striped()
                ->defaultPaginationPageOption(25)
                ->paginationPageOptions([25, 50, 100])
                // Table actions (Create/Edit/Delete modals) read the model label from the table,
                // which otherwise falls back to the English model class name
                ->modelLabel(fn ($livewire): ?string => $livewire instanceof ResourcePage
                    ? $livewire::getResource()::getModelLabel()
                    : null)
                ->pluralModelLabel(fn ($livewire): ?string => $livewire instanceof ResourcePage
                    ? $livewire::getResource()::getPluralModelLabel()
                    : null);
        });

        return $panel
            ->default()
            ->sidebarWidth('15rem') // default ~16rem
            ->sidebarFullyCollapsibleOnDesktop()
            // ->topNavigation() //navigation on top
            ->id('admin')
            ->path(config('app.admin_path')) // path to admin panel in browser, ADMIN_PATH in .env
            ->login(Login::class)
            ->navigation(function (NavigationBuilder $builder): NavigationBuilder {
                return $builder
                    ->items(array_filter([
                        NavigationItem::make(__('admin.tables.dashboard'))
                            ->icon('heroicon-o-home')
                            ->url(Dashboard::getUrl())
                            ->isActiveWhen(fn (): bool => Request::routeIs('filament.admin.pages.dashboard')),
                        NavigationItem::make(__('admin.tables.pages'))
                            ->icon('heroicon-o-document-text')
                            ->url(PageResource::getUrl())
                            ->isActiveWhen(fn (): bool => Request::routeIs('filament.admin.resources.pages.*')),
                        NavigationItem::make(__('admin.tables.languages'))
                            ->icon('heroicon-o-language')
                            ->url(LanguageResource::getUrl())
                            ->isActiveWhen(fn (): bool => Request::routeIs('filament.admin.resources.languages.*')),
                        ModuleGate::isEnabled('news') ? NavigationItem::make(__('admin.tables.news'))
                            ->icon('heroicon-o-newspaper')
                            ->url(NewsResource::getUrl())
                            ->isActiveWhen(fn (): bool => Request::routeIs('filament.admin.resources.news.*')) : null,
                        ModuleGate::isEnabled('feedback') ? NavigationItem::make(__('admin.tables.feedback'))
                            ->icon('heroicon-o-chat-bubble-left-right')
                            ->url(FeedbackResource::getUrl())
                            ->isActiveWhen(fn (): bool => Request::routeIs('filament.admin.resources.feedback.*')) : null,
                        ModuleGate::isEnabled('letter_templates') ? NavigationItem::make(__('admin.tables.letter_templates'))
                            ->icon('heroicon-o-envelope')
                            ->url(LetterTemplateResource::getUrl())
                            ->isActiveWhen(fn (): bool => Request::routeIs('filament.admin.resources.letter-templates.*')) : null,
                        ModuleGate::isEnabled('sms') ? NavigationItem::make(__('admin.tables.sms_templates'))
                            ->icon('heroicon-o-chat-bubble-left-right')
                            ->url(SmsTemplateResource::getUrl())
                            ->isActiveWhen(fn (): bool => Request::routeIs('filament.admin.resources.sms-templates.*')) : null,
                        auth()->user()?->isSuperAdmin() ? NavigationItem::make(__('admin.debug_tools.navigation_label'))
                            ->icon('heroicon-o-command-line')
                            ->url(DebugTools::getUrl())
                            ->isActiveWhen(fn (): bool => Request::routeIs('filament.admin.pages.debug-tools')) : null,
                    ]))
                    ->groups(array_filter([
                        // home page blocks
                        NavigationGroup::make(__('admin.tables.home'))
                            ->items(array_filter([
                                NavigationItem::make(__('admin.tables.announces'))
                                    ->icon('heroicon-o-megaphone')
                                    ->url(AnnounceResource::getUrl())
                                    ->isActiveWhen(fn (): bool => Request::routeIs('filament.admin.resources.home.announces*')),
                                NavigationItem::make(__('admin.tables.sliders'))
                                    ->icon('heroicon-o-photo')
                                    ->url(SliderResource::getUrl())
                                    ->isActiveWhen(fn (): bool => Request::routeIs('filament.admin.resources.home.sliders*')),
                                NavigationItem::make(__('admin.tables.promos'))
                                    ->icon('heroicon-o-rectangle-group')
                                    ->url(PromoResource::getUrl())
                                    ->isActiveWhen(fn (): bool => Request::routeIs('filament.admin.resources.home.promos*')),
                                NavigationItem::make(__('admin.tables.faqs'))
                                    ->icon('heroicon-o-question-mark-circle')
                                    ->url(FaqResource::getUrl())
                                    ->isActiveWhen(fn (): bool => Request::routeIs('filament.admin.resources.home.faqs*')),
                                ModuleGate::isEnabled('review') ? NavigationItem::make(__('admin.tables.reviews'))
                                    ->icon('heroicon-o-star')
                                    ->url(ReviewResource::getUrl())
                                    ->isActiveWhen(fn (): bool => Request::routeIs('filament.admin.resources.home.reviews*')) : null,
                            ])),
                        // catalog
                        ModuleGate::isEnabled('shop') ? NavigationGroup::make(__('admin.tables.catalog'))
                            ->items(array_filter([
                                NavigationItem::make(__('admin.tables.shop_products'))
                                    ->icon('heroicon-o-cube')
                                    ->url(ProductResource::getUrl())
                                    ->isActiveWhen(fn (): bool => Request::routeIs('filament.admin.resources.shop.products*')),
                                NavigationItem::make(__('admin.tables.shop_categories'))
                                    ->icon('heroicon-o-squares-2x2')
                                    ->url(CategoryResource::getUrl())
                                    ->isActiveWhen(fn (): bool => Request::routeIs('filament.admin.resources.shop.categories*')),
                                ModuleGate::isEnabled('shop_brands') ? NavigationItem::make(__('admin.tables.shop_brands'))
                                    ->icon('heroicon-o-tag')
                                    ->url(BrandResource::getUrl())
                                    ->isActiveWhen(fn (): bool => Request::routeIs('filament.admin.resources.shop.brands*')) : null,
                                NavigationItem::make(__('admin.tables.shop_attributes'))
                                    ->icon('heroicon-o-adjustments-horizontal')
                                    ->url(AttributeResource::getUrl())
                                    ->isActiveWhen(fn (): bool => Request::routeIs('filament.admin.resources.shop.attributes*')),
                            ])) : null,
                        // shop
                        ModuleGate::isEnabled('shop_cart') ? NavigationGroup::make(__('admin.tables.shop'))
                            ->items(array_filter([
                                NavigationItem::make(__('admin.tables.shop_order'))
                                    ->icon('heroicon-o-shopping-cart')
                                    ->url(OrderResource::getUrl())
                                    ->isActiveWhen(fn (): bool => Request::routeIs('filament.admin.resources.shop.orders.*')),
                                NavigationItem::make(__('admin.tables.shop_carts'))
                                    ->icon('heroicon-o-shopping-bag')
                                    ->url(CartResource::getUrl())
                                    ->isActiveWhen(fn (): bool => Request::routeIs('filament.admin.resources.cart.carts.*')),
                                ModuleGate::isEnabled('shop_promocodes') ? NavigationItem::make(__('admin.tables.shop_promocodes'))
                                    ->icon('heroicon-o-ticket')
                                    ->url(PromocodeResource::getUrl())
                                    ->isActiveWhen(fn (): bool => Request::routeIs('filament.admin.resources.shop.promocodes.*')) : null,
                            ])) : null,
                        // users
                        NavigationGroup::make(__('admin.tables.users'))
                            ->items(array_filter([
                                NavigationItem::make(__('admin.tables.users'))
                                    ->icon('heroicon-o-users')
                                    ->url(UserResource::getUrl())
                                    ->isActiveWhen(fn (): bool => Request::routeIs('filament.admin.resources.users.*')),
                                ModuleGate::isEnabled('social_profiles') ? NavigationItem::make(__('admin.tables.social_profiles'))
                                    ->icon('heroicon-o-link')
                                    ->url(SocialProfileResource::getUrl())
                                    ->isActiveWhen(fn (): bool => Request::routeIs('filament.admin.resources.social-profiles.*')) : null,
                                ModuleGate::isEnabled('subscriber') ? NavigationItem::make(__('admin.tables.subscriber'))
                                    ->icon('heroicon-o-envelope-open')
                                    ->url(SubscriberResource::getUrl())
                                    ->isActiveWhen(fn (): bool => Request::routeIs('filament.admin.resources.subscribers.*')) : null,
                            ])),

                        ModuleGate::isEnabled('geo') ? NavigationGroup::make(__('admin.tables.geo'))
                            ->items([
                                NavigationItem::make(__('admin.tables.geo_countries'))
                                    ->icon('heroicon-o-globe-alt')
                                    ->url(GeoCountryResource::getUrl())
                                    ->isActiveWhen(fn (): bool => Request::routeIs('filament.admin.resources.geo.geo-countries*')),
                                NavigationItem::make(__('admin.tables.geo_regions'))
                                    ->icon('heroicon-o-map')
                                    ->url(GeoRegionResource::getUrl())
                                    ->isActiveWhen(fn (): bool => Request::routeIs('filament.admin.resources.geo.geo-regions*')),
                                NavigationItem::make(__('admin.tables.geo_cities'))
                                    ->icon('heroicon-o-building-office-2')
                                    ->url(GeoCityResource::getUrl())
                                    ->isActiveWhen(fn (): bool => Request::routeIs('filament.admin.resources.geo.geo-cities*')),
                            ]) : null,

                        NavigationGroup::make(__('admin.tables.logs'))
                            ->items(array_filter([
                                NavigationItem::make(__('admin.tables.logs'))
                                    ->icon('heroicon-o-archive-box')
                                    ->url(LogResource::getUrl())
                                    ->isActiveWhen(fn (): bool => Request::routeIs('filament.admin.resources.logs.*')),
                                NavigationItem::make(__('admin.tables.log_auth'))
                                    ->icon('heroicon-o-finger-print')
                                    ->url(AuthLogResource::getUrl())
                                    ->isActiveWhen(fn (): bool => Request::routeIs('filament.admin.resources.auth-logs.*')),
                                ModuleGate::isEnabled('letter_templates') ? NavigationItem::make(__('admin.tables.log_letters'))
                                    ->icon('heroicon-o-inbox')
                                    ->url(LogLetterResource::getUrl())
                                    ->isActiveWhen(fn (): bool => Request::routeIs('filament.admin.resources.log-letters.*')) : null,
                                ModuleGate::isEnabled('sms') ? NavigationItem::make(__('admin.tables.log_sms'))
                                    ->icon('heroicon-o-chat-bubble-bottom-center-text')
                                    ->url(LogSmsResource::getUrl())
                                    ->isActiveWhen(fn (): bool => Request::routeIs('filament.admin.resources.log-sms.*')) : null,
                                NavigationItem::make(__('admin.tables.log_telegram'))
                                    ->icon('heroicon-o-paper-airplane')
                                    ->url(LogTelegramResource::getUrl())
                                    ->isActiveWhen(fn (): bool => Request::routeIs('filament.admin.resources.log-telegram.*')),
                            ])),
                    ]));
            })
            ->colors([
                'primary' => Color::Sky,
            ])
            ->favicon(asset('favicon.svg') . '?v=2')
            ->resources(array_filter([
                PageResource::class,
                LanguageResource::class,
                AnnounceResource::class,
                SliderResource::class,
                PromoResource::class,
                FaqResource::class,
                ModuleGate::isEnabled('review') ? ReviewResource::class : null,
                ModuleGate::isEnabled('letter_templates') ? LetterTemplateResource::class : null,
                ModuleGate::isEnabled('letter_templates') ? LogLetterResource::class : null,
                LogResource::class,
                AuthLogResource::class,
                UserResource::class,
                ModuleGate::isEnabled('sms') ? SmsTemplateResource::class : null,
                ModuleGate::isEnabled('sms') ? LogSmsResource::class : null,
                LogTelegramResource::class,
                ModuleGate::isEnabled('news') ? NewsResource::class : null,
                ModuleGate::isEnabled('feedback') ? FeedbackResource::class : null,
                ModuleGate::isEnabled('social_profiles') ? SocialProfileResource::class : null,
                ModuleGate::isEnabled('subscriber') ? SubscriberResource::class : null,
                ModuleGate::isEnabled('geo') ? GeoCountryResource::class : null,
                ModuleGate::isEnabled('geo') ? GeoRegionResource::class : null,
                ModuleGate::isEnabled('geo') ? GeoCityResource::class : null,
                ModuleGate::isEnabled('shop') ? ProductResource::class : null,
                ModuleGate::isEnabled('shop') ? CategoryResource::class : null,
                ModuleGate::isEnabled('shop_brands') ? BrandResource::class : null,
                ModuleGate::isEnabled('shop') ? AttributeResource::class : null,
                ModuleGate::isEnabled('shop') ? OrderResource::class : null,
                ModuleGate::isEnabled('shop_cart') ? CartResource::class : null,
                ModuleGate::isEnabled('shop_promocodes') ? PromocodeResource::class : null,
            ]))
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            // No ->discoverWidgets(): order and module gating below have to
            // stay explicit, same as ->resources() above.
            ->widgets(array_filter([
                ModuleGate::isEnabled('shop_cart') ? SalesStatsWidget::class : null,
                ModuleGate::isEnabled('shop_cart') ? SalesChartWidget::class : null,
                ModuleGate::isEnabled('shop_cart') ? LatestOrdersWidget::class : null,
                NeedsAttentionWidget::class,
                ModuleGate::isEnabled('shop') ? CatalogStatsWidget::class : null,
                ModuleGate::isEnabled('shop_cart') ? TopProductsWidget::class : null,
                ModuleGate::isEnabled('shop_promocodes') ? PromocodeStatsWidget::class : null,
                ModuleGate::isEnabled('review') ? ReviewStatsWidget::class : null,
                UserStatsWidget::class,
                SystemStatsWidget::class,
                RecentActivityWidget::class,
            ]))
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
            // persistent: Livewire update requests get the admin locale too
            ->middleware([
                SetAdminLocale::class,
            ], isPersistent: true)
            ->authMiddleware([
                Authenticate::class,
            ])
            ->breadcrumbs(false);
    }
}
