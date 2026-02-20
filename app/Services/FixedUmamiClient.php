<?php

namespace App\Services;

use Schmeits\FilamentUmami\Concerns\UmamiClient;

class FixedUmamiClient extends UmamiClient
{
    /**
     * Fix compatibility with newer Umami API versions:
     * - Strip null params (Umami treats them as active filters)
     * - Map type=url → type=path (renamed in newer API)
     */
    public function callApi(string $url, array $options): array
    {
        $options = array_filter($options, fn ($value) => ! is_null($value));

        return parent::callApi($url, $options);
    }

    /**
     * Fix endpoint-specific parameter issues for newer Umami API.
     */
    public function callWebsiteApi(string $url, array $options): array
    {
        // Stats endpoint only accepts startAt and endAt
        if ($url === 'stats') {
            $options = array_intersect_key($options, array_flip(['startAt', 'endAt']));
        }

        // Metrics endpoint: type=url was renamed to type=path
        if ($url === 'metrics' && ($options['type'] ?? null) === 'url') {
            $options['type'] = 'path';
        }

        return parent::callWebsiteApi($url, $options);
    }
}
