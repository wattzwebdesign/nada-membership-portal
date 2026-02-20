<?php

namespace App\Services;

use Schmeits\FilamentUmami\Concerns\UmamiClient;

class FixedUmamiClient extends UmamiClient
{
    /**
     * Override to strip null/empty query parameters that newer Umami versions
     * interpret as active filters (e.g. url= → "filter for empty URL").
     */
    public function callApi(string $url, array $options): array
    {
        $options = array_filter($options, fn ($value) => ! is_null($value));

        return parent::callApi($url, $options);
    }
}
