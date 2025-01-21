@component('mail::message')
# Dear {{ $name }},

There is a new withdrawal request for you account:

# Amount: ${{ $amount }}
# Currency: {{ $currency }}
# Wallet: {{ $wallet }}

@component('mail::button', ['url' => $link, 'color' => 'primary'])
    Confirm
@endcomponent

<small>If link above doesn't work, You copy and paste address below:<br>
{{$link}}
</small>

Thanks,<br>
{{ config('app.name') }}
@endcomponent
