@component('mail::message')
# Dear {{ $name }},

Your withdrawal request paid:

# Amount: ${{ $amount }}
# Currency: {{ $currency }}
# Wallet: {{ $wallet }}

Thanks,<br>
{{ config('app.name') }}
@endcomponent
