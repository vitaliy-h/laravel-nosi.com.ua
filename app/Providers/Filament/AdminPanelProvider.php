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
use Illuminate\Support\Facades\Request;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        Table::configureUsing(function (Table $table): void {
            $table
                ->recordUrl(fn () => null)//Do not open view page when clicking a table row
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
            ->login()
            ->navigation(function (NavigationBuilder $builder): NavigationBuilder {
                return $builder
                    ->items([
                        NavigationItem::make(__('admin.tables.dashboard'))
                            ->icon('heroicon-o-home')
                            ->url(Dashboard::getUrl())
                            ->isActiveWhen(fn (): bool => Request::routeIs('filament.admin.pages.dashboard')),
                        NavigationItem::make(__('admin.tables.pages'))
                            ->icon('heroicon-o-document-text')
                            ->url(\App\Filament\Resources\Pages\PageResource::getUrl())
                            ->isActiveWhen(fn (): bool => Request::routeIs('filament.admin.resources.pages.*')),
                        NavigationItem::make(__('admin.tables.languages'))
                            ->icon('heroicon-o-language')
                            ->url(\App\Filament\Resources\Languages\LanguageResource::getUrl())
                            ->isActiveWhen(fn (): bool => Request::routeIs('filament.admin.resources.languages.*')),
                        NavigationItem::make(__('admin.tables.news'))
                            ->icon('heroicon-o-newspaper')
                            ->url(\App\Filament\Resources\News\NewsResource::getUrl())
                            ->isActiveWhen(fn (): bool => Request::routeIs('filament.admin.resources.news.*')),
                    ])
                    ->groups([
                        // shop
                        NavigationGroup::make(__('admin.tables.shop'))
                            ->items([
                                NavigationItem::make(__('admin.tables.shop_products'))
                                    ->icon('heroicon-o-cube')
                                    ->url(\App\Filament\Resources\Shop\Products\ProductResource::getUrl())
                                    ->isActiveWhen(fn (): bool => Request::routeIs('filament.admin.resources.shop.products*')),
                                NavigationItem::make(__('admin.tables.shop_categories'))
                                    ->icon('heroicon-o-squares-2x2')
                                    ->url(\App\Filament\Resources\Shop\Categories\CategoryResource::getUrl())
                                    ->isActiveWhen(fn (): bool => Request::routeIs('filament.admin.resources.shop.categories*')),
                                NavigationItem::make(__('admin.tables.shop_brands'))
                                    ->icon('heroicon-o-tag')
                                    ->url(\App\Filament\Resources\Shop\Brands\BrandResource::getUrl())
                                    ->isActiveWhen(fn (): bool => Request::routeIs('filament.admin.resources.shop.brands*')),
                            ]),
                        // users
                        NavigationGroup::make(__('admin.tables.users'))
                            ->items([
                                NavigationItem::make(__('admin.tables.users'))
                                    ->icon('heroicon-o-users')
                                    ->url(\App\Filament\Resources\Users\UserResource::getUrl())
                                    ->isActiveWhen(fn (): bool => Request::routeIs('filament.admin.resources.users*')),
                            ]),

                        NavigationGroup::make(__('admin.tables.letters'))
                            ->items([
                                NavigationItem::make(__('admin.tables.letter_templates'))
                                    ->icon('heroicon-o-envelope')
                                    ->url(\App\Filament\Resources\LetterTemplates\LetterTemplateResource::getUrl())
                                    ->isActiveWhen(fn (): bool => Request::routeIs('filament.admin.resources.letter-templates.*')),
                                NavigationItem::make(__('admin.tables.log_letters'))
                                    ->icon('heroicon-o-inbox')
                                    ->url(\App\Filament\Resources\LogLetters\LogLetterResource::getUrl())
                                    ->isActiveWhen(fn (): bool => Request::routeIs('filament.admin.resources.log-letters.*')),
                            ]),

                        NavigationGroup::make(__('admin.tables.logs'))
                            ->items([
                                NavigationItem::make(__('admin.tables.logs'))
                                    ->icon('heroicon-o-archive-box')
                                    ->url(\App\Filament\Resources\Logs\LogResource::getUrl())
                                    ->isActiveWhen(fn (): bool => Request::routeIs('filament.admin.resources.logs.*')),
                            ]),
                    ]);
            })
            ->colors([
                'primary' => Color::Sky,
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
