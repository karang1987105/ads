@component('mail::message')
# Dear {{ $name }},

The ad <b>{{ $ad->getTitle() }}</b> has been declined.

You would want to [contact us]({{ route('contact.create') }}) for more details.

Thanks,<br>
{{ config('app.name') }}
@endcomponent
