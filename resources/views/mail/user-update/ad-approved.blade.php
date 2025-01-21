@component('mail::message')
# Dear {{ $name }},

The ad <b>{{ $ad->getTitle() }}</b> has been approved.

Thanks,<br>
{{ config('app.name') }}
@endcomponent
