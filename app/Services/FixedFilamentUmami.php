<?php

namespace App\Services;

use Schmeits\FilamentUmami\FilamentUmami;

class FixedFilamentUmami extends FilamentUmami
{
    public function __construct()
    {
        $this->client = new FixedUmamiClient;
    }
}
