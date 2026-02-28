<?php

namespace App\Providers\Filament;

use App\Livewire\Breezy\CustomPersonalInfo;
use DiogoGPinto\AuthUIEnhancer\AuthUIEnhancerPlugin;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
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
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Filament\Support\Facades\FilamentAsset;
use Filament\Support\Assets\Css;
use Filament\Support\Facades\FilamentColor;
use Jeffgreco13\FilamentBreezy\BreezyCore;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        FilamentColor::register([
            'danger' => Color::Red,
            'gray' => Color::Zinc,
            'info' => Color::Blue,
            'primary' => [
                // Light Mode — hue kuning #F8B811
                50  => 'oklch(0.97 0.075 82.8)',
                100 => 'oklch(0.93 0.078 82.8)',
                200 => 'oklch(0.87 0.083 82.8)',
                300 => 'oklch(0.78 0.090 82.8)',
                400 => 'oklch(0.67 0.098 82.8)',
                500 => 'oklch(0.58 0.105 82.8)',
                // Dark Mode — hue biru #122966
                600 => 'oklch(0.50 0.166 264.8)',
                700 => 'oklch(0.43 0.158 264.8)',
                800 => 'oklch(0.36 0.150 264.8)',
                900 => 'oklch(0.28 0.141 264.8)',
                950 => 'oklch(0.20 0.131 264.8)',
            ],
            'success' => Color::Green,
            'warning' => Color::Amber,
        ]);

        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->login()
            ->registration()
            ->brandName('Dabraka')
            ->brandLogo(asset('assets/images/logos/logo-light.svg'))
            ->darkModeBrandLogo(asset('assets/images/logos/logo-dark.svg'))
            ->brandLogoHeight('2.5rem')
            ->favicon(asset('favicon.png'))
            ->sidebarFullyCollapsibleOnDesktop()
            ->globalSearch(false)
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->plugins([
                FilamentShieldPlugin::make(),
                BreezyCore::make()
                    ->myProfile(shouldRegisterUserMenu: true,)
                    ->enableBrowserSessions(condition: true)
                    ->myProfileComponents([
                        'personal_info' => \App\Livewire\CustomPersonalInfo::class,
                    ]),
                AuthUIEnhancerPlugin::make()
                    ->showEmptyPanelOnMobile(false)
                    ->formPanelPosition('right')
                    ->formPanelWidth('40%')
                    ->emptyPanelBackgroundImageOpacity('70%')
                    ->emptyPanelBackgroundImageUrl('https://images.pexels.com/photos/3646172/pexels-photo-3646172.jpeg'),
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AccountWidget::class,
            ])
            ->databaseNotifications()
            ->databaseNotificationsPolling('30s')
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
            ]);
    }
}
