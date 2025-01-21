@component('mail::message')
# Dear {{ $name }},

We want to inform you that your account has been activated.

You would want to [contact us]({{ route('contact.create') }}) for more details.

Thanks,<br>
{{ config('app.name') }}
@endcomponent
