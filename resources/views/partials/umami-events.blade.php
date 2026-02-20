@if(session('umami_event'))
<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof umami !== 'undefined') {
        umami.track('{{ session('umami_event') }}');
    }
});
</script>
@endif
