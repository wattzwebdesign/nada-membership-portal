<div x-data x-init="
    if (!window._googlePlacesLoaded && '{{ config('services.google.maps_api_key') }}') {
        window._googlePlacesLoaded = true;
        const script = document.createElement('script');
        script.src = 'https://maps.googleapis.com/maps/api/js?key={{ config('services.google.maps_api_key') }}&libraries=places';
        script.async = true;
        script.defer = true;
        script.onload = () => $dispatch('google-places-ready');
        document.head.appendChild(script);
    } else if (window.google && window.google.maps && window.google.maps.places) {
        $nextTick(() => $dispatch('google-places-ready'));
    }
" x-on:google-places-ready.window="
    $nextTick(() => {
        const input = document.querySelector('input[wire\\:model\\.live=\'data.location_address\']')
            || document.querySelector('[id$=\'data.location_address\'] input')
            || document.getElementById('data.location_address');
        if (!input || input._autocompleteAttached) return;
        input._autocompleteAttached = true;
        input.setAttribute('autocomplete', 'off');

        const autocomplete = new google.maps.places.Autocomplete(input, {
            types: ['address'],
            fields: ['address_components', 'geometry', 'name'],
        });

        autocomplete.addListener('place_changed', () => {
            const place = autocomplete.getPlace();
            if (!place.address_components) return;

            let street_number = '', route = '', city = '', state = '', zip = '', country = '', lat = '', lng = '';
            let locationName = place.name || '';

            for (const component of place.address_components) {
                const type = component.types[0];
                if (type === 'street_number') street_number = component.long_name;
                if (type === 'route') route = component.long_name;
                if (type === 'locality' || type === 'postal_town') city = component.long_name;
                if (type === 'administrative_area_level_1') state = component.short_name;
                if (type === 'postal_code') zip = component.long_name;
                if (type === 'country') country = component.short_name;
            }

            if (!city) {
                for (const component of place.address_components) {
                    if (component.types.includes('sublocality_level_1') || component.types.includes('neighborhood')) {
                        city = component.long_name;
                        break;
                    }
                }
            }

            const address = [street_number, route].filter(Boolean).join(' ');

            if (place.geometry && place.geometry.location) {
                lat = place.geometry.location.lat();
                lng = place.geometry.location.lng();
            }

            // Set Livewire form state
            $wire.set('data.location_address', address);
            $wire.set('data.city', city);
            $wire.set('data.state', state);
            $wire.set('data.zip', zip);
            $wire.set('data.country', country);
            if (lat) $wire.set('data.latitude', lat);
            if (lng) $wire.set('data.longitude', lng);

            // If location_name is empty, fill it with the place name
            const currentName = $wire.get('data.location_name');
            if (!currentName && locationName && locationName !== address) {
                $wire.set('data.location_name', locationName);
            }
        });
    });
">
</div>
