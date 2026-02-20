<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use App\Filament\Pages\Auth\Login;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use App\Models\SiteSetting;
use Filament\Support\Colors\Color;
use Illuminate\Support\HtmlString;
use Filament\View\PanelsRenderHook;
use Filament\Widgets;
use Schmeits\FilamentUmami\FilamentUmamiPlugin;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
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
            ->login(Login::class)
            ->brandName('NADA Admin')
            ->brandLogo(fn () => new HtmlString('<img src="' . asset('NADAWebsiteLogo.svg') . '" alt="NADA Admin" style="height: 2.5rem;" />'))
            ->brandLogoHeight('2.5rem')
            ->favicon(asset('favicon.png'))
            ->colors([
                'primary' => Color::hex('#1C3519'),
                'danger' => Color::Red,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->plugins([
                FilamentUmamiPlugin::make(),
            ])
            ->widgets([])
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
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn () => new HtmlString('
                    <style>
                        /* Stronger visual separation between global search resource groups */
                        .fi-global-search-result-group + .fi-global-search-result-group {
                            border-top: 2px solid rgb(209 213 219);
                            margin-top: 2px;
                        }
                        .dark .fi-global-search-result-group + .fi-global-search-result-group {
                            border-top-color: rgb(55 65 81);
                        }
                        /* Bolder group headers */
                        .fi-global-search-result-group > div:first-child {
                            background-color: rgb(243 244 246);
                        }
                        .dark .fi-global-search-result-group > div:first-child {
                            background-color: rgb(31 41 55);
                        }
                        .fi-global-search-result-group > div:first-child h3 {
                            font-size: 0.7rem;
                            text-transform: uppercase;
                            letter-spacing: 0.06em;
                            padding-top: 0.5rem;
                            padding-bottom: 0.5rem;
                        }
                    </style>
                '),
            )
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn () => SiteSetting::umamiEnabled() && SiteSetting::umamiScriptUrl()
                    ? new HtmlString('<script defer src="' . e(SiteSetting::umamiScriptUrl()) . '" data-website-id="' . e(SiteSetting::umamiWebsiteId()) . '"></script>')
                    : '',
            )
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn () => new HtmlString('
                    <style>
                        /* Chat widget brand colors (not in Filament\'s Tailwind build) */
                        .bg-brand-primary { background-color: #1C3519; }
                        .bg-brand-secondary { background-color: #AD7E07; }
                        .focus\:ring-brand-primary:focus { --tw-ring-color: #1C3519; }
                        /* Guide overlay for "Show Me Where" feature */
                        @keyframes nada-guide-pulse {
                            0%, 100% { box-shadow: 0 0 0 0 rgba(173, 126, 7, 0.5); }
                            50% { box-shadow: 0 0 0 8px rgba(173, 126, 7, 0); }
                        }
                        .guide-overlay {
                            position: fixed;
                            inset: 0;
                            background: rgba(0, 0, 0, 0.4);
                            z-index: 40;
                            animation: guide-fade-in 0.3s ease;
                        }
                        @keyframes guide-fade-in {
                            from { opacity: 0; }
                            to { opacity: 1; }
                        }
                        [data-guide-active] {
                            animation: nada-guide-pulse 1.5s ease-in-out 3;
                            outline: 2px solid #AD7E07;
                            outline-offset: 2px;
                            border-radius: 4px;
                        }
                    </style>
                '),
            )
            ->renderHook(
                PanelsRenderHook::BODY_END,
                fn () => view('partials.support-chat')->render(),
            );
    }
}
