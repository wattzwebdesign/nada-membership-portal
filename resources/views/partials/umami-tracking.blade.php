@if(\App\Models\SiteSetting::umamiEnabled() && \App\Models\SiteSetting::umamiScriptUrl())
<script defer src="{{ \App\Models\SiteSetting::umamiScriptUrl() }}" data-website-id="{{ \App\Models\SiteSetting::umamiWebsiteId() }}"></script>
@endif
