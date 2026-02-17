@props(['url'])
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
<img src="{{ asset('images/certificates/nada-logo.png') }}" alt="{{ config('app.name') }}" style="max-height: 60px; width: auto; margin-bottom: 10px;"><br>
{!! $slot !!}
</a>
</td>
</tr>
