@component('mail::message')
# Dear {{ $name }},

Your withdrawal request with details below has been confirmed:

# Amount: ${{ $amount }}
# Currency: {{ $currency }}
# Wallet: {{ $wallet }}

Thanks,<br>
{{ config('app.name') }}
@endcomponent
