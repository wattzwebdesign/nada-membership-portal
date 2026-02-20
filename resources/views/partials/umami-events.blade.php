@if(session('umami_event'))
<script>
if (typeof umami !== 'undefined') {
    umami.track('{{ session('umami_event') }}');
}
</script>
@endif
