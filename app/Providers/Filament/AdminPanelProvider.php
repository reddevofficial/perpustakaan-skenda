<?php

namespace App\Providers\Filament;

use App\Filament\Pages\CustomLogin;
use Filament\Enums\ThemeMode;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\Width;
use Filament\View\PanelsRenderHook;
use Filament\Widgets\AccountWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login(CustomLogin::class)
            ->brandName('Perpustakaan SMKN 2')
            ->brandLogo(fn () => view('filament.admin.logo'))
            ->favicon(asset('images/favicon.png'))
            ->font('Inter')
            ->colors([
                'primary' => Color::Indigo,
                'danger' => Color::Red,
                'gray' => Color::Zinc,
                'info' => Color::Blue,
                'success' => Color::Emerald,
                'warning' => Color::Amber,
            ])
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->sidebarCollapsibleOnDesktop()
            ->sidebarWidth('16rem')
            ->collapsedSidebarWidth('5.5rem')
            ->collapsibleNavigationGroups(true)
            ->darkMode(false)
            ->defaultThemeMode(ThemeMode::Light)
            ->maxContentWidth(Width::Full)
            ->unsavedChangesAlerts()
            ->navigationGroups([
                NavigationGroup::make()
                    ->label('Perpustakaan'),
                NavigationGroup::make()
                    ->label('Transaksi'),
                NavigationGroup::make()
                    ->label('Laporan'),
                NavigationGroup::make()
                    ->label('Sistem')
                    ->collapsed(),
            ])
            ->renderHook(
                PanelsRenderHook::SIMPLE_LAYOUT_START,
                fn () => view('filament.admin.login-header')
            )
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                AccountWidget::class,
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
            ]);
    }
}
