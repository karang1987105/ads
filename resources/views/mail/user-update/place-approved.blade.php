@component('mail::message')
# Dear {{ $name }},

The place <b>{{ $place }}</b> has been approved.

Thanks,<br>
{{ config('app.name') }}
@endcomponent
