<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Filament\Navigation\NavigationBuilder;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;
use Filament\Tables\Table;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        Table::configureUsing(function (Table $table): void {
            $table->recordUrl(fn () => null);//Do not open view page when clicking a table row.
        });
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->navigation(function (NavigationBuilder $builder): NavigationBuilder {
                return $builder
                    ->items([
                        NavigationItem::make(__('admin.tables.dashboard'))
                            ->icon('heroicon-o-home')
                            ->url(Dashboard::getUrl()),
                        NavigationItem::make(__('admin.tables.pages'))
                            ->icon('heroicon-o-document-text')
                            ->url(\App\Filament\Resources\Pages\PageResource::getUrl()),
                        NavigationItem::make(__('admin.tables.languages'))
                            ->icon('heroicon-o-language')
                            ->url(\App\Filament\Resources\Languages\LanguageResource::getUrl()),
                        NavigationItem::make(__('admin.tables.news'))
                            ->icon('heroicon-o-newspaper')
                            ->url(\App\Filament\Resources\News\NewsResource::getUrl()),
                    ])
                    ->groups([
                        // shop
                        NavigationGroup::make(__('admin.tables.shop'))
                            ->items([
                                NavigationItem::make(__('admin.tables.shop_products'))
                                    ->icon('heroicon-o-cube')
                                    ->url(\App\Filament\Resources\Shop\Products\ProductResource::getUrl()),
                                NavigationItem::make(__('admin.tables.shop_categories'))
                                    ->icon('heroicon-o-squares-2x2')
                                    ->url(\App\Filament\Resources\Shop\Categories\CategoryResource::getUrl()),
                                NavigationItem::make(__('admin.tables.shop_brands'))
                                    ->icon('heroicon-o-tag')
                                    ->url(\App\Filament\Resources\Shop\Brands\BrandResource::getUrl()),
                            ]),
                        // users
                        NavigationGroup::make(__('admin.tables.users'))
                            ->items([
                                NavigationItem::make(__('admin.tables.users'))
                                    ->icon('heroicon-o-users')
                                    ->url(\App\Filament\Resources\Users\UserResource::getUrl()),
                            ]),
                    ]);
            })
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
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
