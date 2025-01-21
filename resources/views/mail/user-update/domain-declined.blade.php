@component('mail::message')
<b>Hello {{ $name }},</b>

We would like to inform you that your domain <a href="{{ $domain }}">{{ $domain }}</a> has been declined.

If you have any further questions, please <a href="{{ config('app.url') }}/contact">contact us</a>.

<b>Best regards</b>,<br>
{{ config('app.name') }}
@endcomponent
