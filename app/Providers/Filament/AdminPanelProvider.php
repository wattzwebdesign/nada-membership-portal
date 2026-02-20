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
                        /* ── Chat widget styles (scoped to avoid Filament conflicts) ── */
                        /* Brand colors */
                        [x-data="supportChat"] .bg-brand-primary, [x-data="supportChat"].bg-brand-primary { background-color: #1C3519 !important; }
                        [x-data="supportChat"] .bg-brand-secondary { background-color: #AD7E07 !important; }

                        /* Chat panel */
                        [x-data="supportChat"] > div:first-child {
                            margin-bottom: 1rem;
                            width: 380px;
                            max-width: calc(100vw - 2rem);
                            border-radius: 1rem;
                            box-shadow: 0 25px 50px -12px rgba(0,0,0,.25);
                            overflow: hidden;
                            display: flex;
                            flex-direction: column;
                            background-color: #fff;
                            border: 1px solid #e5e7eb;
                        }

                        /* Header */
                        [x-data="supportChat"] > div:first-child > div:first-child {
                            display: flex;
                            align-items: center;
                            justify-content: space-between;
                            padding: 0.75rem 1rem;
                            color: #fff;
                            flex-shrink: 0;
                            background-color: #1C3519;
                        }
                        [x-data="supportChat"] > div:first-child > div:first-child .font-semibold { font-weight: 600; }
                        [x-data="supportChat"] > div:first-child > div:first-child .text-sm { font-size: 0.875rem; }
                        [x-data="supportChat"] > div:first-child > div:first-child button {
                            padding: 0.375rem;
                            border-radius: 0.5rem;
                            background: transparent;
                            border: none;
                            color: #fff;
                            cursor: pointer;
                        }
                        [x-data="supportChat"] > div:first-child > div:first-child button:hover { background: rgba(255,255,255,.2); }
                        [x-data="supportChat"] .gap-1 { gap: 0.25rem; }
                        [x-data="supportChat"] .gap-2 { gap: 0.5rem; }

                        /* Messages area */
                        [x-data="supportChat"] [x-ref="chatMessages"] {
                            flex: 1 1 0%;
                            overflow-y: auto;
                            padding: 0.75rem 1rem;
                        }
                        [x-data="supportChat"] [x-ref="chatMessages"] > * + * { margin-top: 0.75rem; }
                        [x-data="supportChat"] .flex { display: flex; }
                        [x-data="supportChat"] .items-center { align-items: center; }
                        [x-data="supportChat"] .justify-start { justify-content: flex-start; }
                        [x-data="supportChat"] .justify-end { justify-content: flex-end; }

                        /* Message bubbles */
                        [x-data="supportChat"] .rounded-2xl { border-radius: 1rem; }
                        [x-data="supportChat"] .rounded-tl-sm { border-top-left-radius: 0.125rem; }
                        [x-data="supportChat"] .rounded-tr-sm { border-top-right-radius: 0.125rem; }
                        [x-data="supportChat"] .max-w-\\[85\\%\\] { max-width: 85%; }
                        [x-data="supportChat"] .px-4 { padding-left: 1rem; padding-right: 1rem; }
                        [x-data="supportChat"] .py-2\\.5 { padding-top: 0.625rem; padding-bottom: 0.625rem; }
                        [x-data="supportChat"] .py-3 { padding-top: 0.75rem; padding-bottom: 0.75rem; }
                        [x-data="supportChat"] .px-3 { padding-left: 0.75rem; padding-right: 0.75rem; }
                        [x-data="supportChat"] .text-sm { font-size: 0.875rem; line-height: 1.25rem; }
                        [x-data="supportChat"] .text-xs { font-size: 0.75rem; line-height: 1rem; }
                        [x-data="supportChat"] .text-base { font-size: 1rem; line-height: 1.5rem; }
                        [x-data="supportChat"] .bg-gray-100 { background-color: #f3f4f6; }
                        [x-data="supportChat"] .text-gray-800 { color: #1f2937; }
                        [x-data="supportChat"] .text-gray-500 { color: #6b7280; }
                        [x-data="supportChat"] .text-white { color: #fff; }
                        [x-data="supportChat"] .mt-2 { margin-top: 0.5rem; }
                        [x-data="supportChat"] p { margin: 0; }

                        /* Typing indicator */
                        [x-data="supportChat"] .w-2 { width: 0.5rem; }
                        [x-data="supportChat"] .h-2 { height: 0.5rem; }
                        [x-data="supportChat"] .bg-gray-400 { background-color: #9ca3af; }
                        [x-data="supportChat"] .rounded-full { border-radius: 9999px; }
                        [x-data="supportChat"] .animate-bounce {
                            animation: nada-chat-bounce 1s infinite;
                        }
                        @keyframes nada-chat-bounce {
                            0%, 100% { transform: translateY(0); }
                            50% { transform: translateY(-25%); }
                        }

                        /* Error */
                        [x-data="supportChat"] .bg-red-50 { background-color: #fef2f2; }
                        [x-data="supportChat"] .border-red-100 { border-color: #fee2e2; }
                        [x-data="supportChat"] .text-red-600 { color: #dc2626; }
                        [x-data="supportChat"] .border-t { border-top: 1px solid; }
                        [x-data="supportChat"] .border-gray-200 { border-color: #e5e7eb; }
                        [x-data="supportChat"] .shrink-0 { flex-shrink: 0; }

                        /* Input area */
                        [x-data="supportChat"] input[type="text"] {
                            flex: 1 1 0%;
                            border-radius: 9999px;
                            border: 1px solid #d1d5db;
                            padding: 0.5rem 1rem;
                            font-size: 0.875rem;
                            outline: none;
                            background: #fff;
                            color: #1f2937;
                            min-width: 0;
                        }
                        [x-data="supportChat"] input[type="text"]:focus {
                            box-shadow: 0 0 0 2px #1C3519;
                            border-color: transparent;
                        }
                        @media (min-width: 640px) {
                            [x-data="supportChat"] input[type="text"] { font-size: 0.875rem; }
                        }

                        /* Send button */
                        [x-data="supportChat"] button.shrink-0 {
                            flex-shrink: 0;
                            width: 2.25rem;
                            height: 2.25rem;
                            border-radius: 9999px;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            color: #fff;
                            background-color: #AD7E07;
                            border: none;
                            cursor: pointer;
                            transition: opacity .15s;
                        }
                        [x-data="supportChat"] button.shrink-0:hover { opacity: .9; }
                        [x-data="supportChat"] button.shrink-0:disabled { opacity: .5; }
                        [x-data="supportChat"] .rotate-45 { transform: rotate(45deg); }

                        /* Floating button */
                        [x-data="supportChat"] > button:last-child {
                            margin-left: auto;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            width: 3.5rem;
                            height: 3.5rem;
                            border-radius: 9999px;
                            color: #fff;
                            background-color: #1C3519;
                            box-shadow: 0 10px 15px -3px rgba(0,0,0,.1), 0 4px 6px -4px rgba(0,0,0,.1);
                            border: none;
                            cursor: pointer;
                            transition: transform .15s, box-shadow .15s;
                        }
                        [x-data="supportChat"] > button:last-child:hover {
                            transform: scale(1.05);
                            box-shadow: 0 20px 25px -5px rgba(0,0,0,.1), 0 8px 10px -6px rgba(0,0,0,.1);
                        }

                        /* SVG icons */
                        [x-data="supportChat"] .h-4 { height: 1rem; }
                        [x-data="supportChat"] .w-4 { width: 1rem; }
                        [x-data="supportChat"] .h-5 { height: 1.25rem; }
                        [x-data="supportChat"] .w-5 { width: 1.25rem; }
                        [x-data="supportChat"] .h-6 { height: 1.5rem; }
                        [x-data="supportChat"] .w-6 { width: 1.5rem; }

                        /* Links in rendered markdown */
                        [x-data="supportChat"] a { color: #2563eb; text-decoration: underline; }
                        [x-data="supportChat"] a:hover { color: #1e40af; }

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
                fn () => new HtmlString(view('partials.support-chat')->render()),
            );
    }
}
