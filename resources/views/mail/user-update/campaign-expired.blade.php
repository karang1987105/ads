@component('mail::message')
# Dear {{ $name }},

A campaign of <b>{{ $campaign->ad->getTitle() }}</b> has been expired.

Thanks,<br>
{{ config('app.name') }}
@endcomponent
