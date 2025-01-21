@component('mail::message')
# Dear {{ $name }},

The place <b>{{ $place }}</b> has been declined.

You would want to [contact us]({{ route('contact.create') }}) for more details.

Thanks,<br>
{{ config('app.name') }}
@endcomponent
