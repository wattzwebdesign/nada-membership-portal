<?php

namespace App\Filament\Pages\Auth;

use Filament\Pages\Auth\Login as BaseLogin;
use Illuminate\Contracts\Support\Htmlable;

class Login extends BaseLogin
{
    public function getHeading(): string|Htmlable
    {
        return 'Admin Login';
    }

    public function getSubheading(): string|Htmlable|null
    {
        return 'Authorized administrators only';
    }
}
