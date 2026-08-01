<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Dashboard;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\MenuItem;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('')
            ->login()
            ->brandName('miniHRM HRM')
            ->sidebarCollapsibleOnDesktop()
            ->collapsibleNavigationGroups(true)
            ->sidebarWidth('14rem')
            ->renderHook(
                'panels::head.end',
                fn (): HtmlString => new HtmlString('
                    <style>
                        /* === COMPACT SIDEBAR: tighter spacing inside 14rem === */

                        /* Sidebar background — light + dark mode */
                        .fi-sidebar {
                            background-color: #f3f4f6 !important;
                        }
                        :is(.dark) .fi-sidebar {
                            background-color: #1f2937 !important;
                        }
                        .fi-sidebar-header {
                            background-color: #f3f4f6 !important;
                            padding-inline: 0.75rem !important;
                        }
                        :is(.dark) .fi-sidebar-header {
                            background-color: #1f2937 !important;
                        }

                        /* Collapse button: push right */
                        .fi-sidebar-header .fi-icon-btn {
                            margin-inline: auto 0 !important;
                        }

                        /* Logo text smaller to fit */
                        .fi-sidebar-header .fi-logo {
                            font-size: 0.875rem !important;
                        }

                        /* Nav item compact padding */
                        .fi-sidebar-item-label {
                            font-size: 0.8125rem !important;
                        }
                        .fi-sidebar-item-button {
                            padding-block: 0.375rem !important;
                            padding-inline: 0.625rem !important;
                        }
                        .fi-sidebar-nav a.fi-sidebar-item {
                            padding-inline: 0.5rem !important;
                        }
                        .fi-sidebar-nav-group-items {
                            padding-inline: 0.25rem !important;
                        }

                        /* Sidebar item text color in dark mode */
                        :is(.dark) .fi-sidebar-item-label {
                            color: #d1d5db !important;
                        }
                        :is(.dark) .fi-sidebar-item-button:hover {
                            background-color: #374151 !important;
                        }

                        /* === THIN SCROLLBARS === */
                        .fi-sidebar-nav::-webkit-scrollbar {
                            width: 4px !important;
                        }
                        .fi-sidebar-nav::-webkit-scrollbar-track {
                            background: transparent !important;
                        }
                        .fi-sidebar-nav::-webkit-scrollbar-thumb {
                            background: rgba(100, 100, 100, 0.25) !important;
                            border-radius: 8px !important;
                        }
                        .fi-sidebar-nav::-webkit-scrollbar-thumb:hover {
                            background: rgba(100, 100, 100, 0.4) !important;
                        }

                        ::-webkit-scrollbar {
                            width: 5px !important;
                            height: 5px !important;
                        }
                        ::-webkit-scrollbar-track {
                            background: transparent !important;
                        }
                        ::-webkit-scrollbar-thumb {
                            background: rgba(100, 100, 100, 0.2) !important;
                            border-radius: 8px !important;
                        }
                        ::-webkit-scrollbar-thumb:hover {
                            background: rgba(100, 100, 100, 0.35) !important;
                        }

                        /* Dark mode scrollbars */
                        :is(.dark) ::-webkit-scrollbar-thumb {
                            background: rgba(255, 255, 255, 0.15) !important;
                        }
                        :is(.dark) ::-webkit-scrollbar-thumb:hover {
                            background: rgba(255, 255, 255, 0.25) !important;
                        }
                    </style>
                ')
            )
            ->colors([
                'primary' => Color::Indigo,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->navigationGroups([
                'Tasks',
                'Organization',
                'Employees',
                'Attendance',
                'Leave',
                'Payroll',
                'Finance',
                'Administration',
            ])
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                \App\Filament\Widgets\FinanceOverview::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->userMenuItems([
                MenuItem::make()
                    ->label('My Profile')
                    ->icon('heroicon-o-user-circle')
                    ->url('/profile'),
            ])
            ->plugins([
                FilamentShieldPlugin::make(),
            ]);
    }
}
