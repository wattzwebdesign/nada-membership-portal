<?php

namespace App\Services;

use Schmeits\FilamentUmami\Concerns\UmamiClient;

class FixedUmamiClient extends UmamiClient
{
    /**
     * Fix compatibility with newer Umami API versions:
     * - Strip null params (Umami treats them as active filters)
     * - Normalize response formats for stats and active endpoints
     */
    public function callApi(string $url, array $options): array
    {
        $options = array_filter($options, fn ($value) => ! is_null($value));

        $result = parent::callApi($url, $options);

        // Active endpoint: new API returns {"visitors": N}, widget expects {"x": N}
        if (str_contains($url, '/active') && isset($result['visitors']) && ! isset($result['x'])) {
            $result['x'] = $result['visitors'];
        }

        // Stats endpoint: new API returns flat values, widget expects nested {"value": N}
        if (str_contains($url, '/stats')) {
            foreach (['pageviews', 'visitors', 'visits', 'bounces', 'totaltime'] as $key) {
                if (isset($result[$key]) && ! is_array($result[$key])) {
                    $result[$key] = ['value' => $result[$key]];
                }
            }
        }

        return $result;
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
