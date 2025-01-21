<x-form name="add" method="POST">
    @isset($currency)
        <x-slot name="action">{{ route('admin.currencies.update', compact('currency'), false) }}</x-slot>
        <x-slot name="method">PUT</x-slot>
        <x-slot name="submit">Ads.item.submitForm(this)</x-slot>
    @else
        <x-slot name="action">{{ route('admin.currencies.store', absolute: false) }}</x-slot>
        <x-slot name="submit">Ads.list.submitAddForm(this)</x-slot>
    @endisset
    <x-form-column>
        <x-input.text name="name" required value="{{ isset($currency) ? $currency->name : '' }}"/>
        <x-input.text name="id" label="Currency ID" required value="{{ isset($currency) ? $currency->id : '' }}"/>
        <x-input.text name="coingecko" label="Price Tracker ID" required
                      value="{{ isset($currency) ? $currency->coingecko : '' }}"/>
    </x-form-column>
    <x-form-column>
        <x-input.text name="bonus" label="Bonus %" value="{{ isset($currency) ? $currency->bonus : '' }}"/>
        <x-input.text name="exchange_rate" label="Exchange Rate"
                      value="{{ isset($currency) ? ($currency->exchange_rate > 0 ? 1 / $currency->exchange_rate : 0) : '' }}"/>
        <x-input.text name="rpc_server" label="RPC Server URL" required
                      value="{{ isset($currency) ? $currency->rpc_server : '' }}"/>
        <x-input.select name="rpc_block_count_interval" label="RPC Server Check Interval"
                      value="{{ isset($currency) ? $currency->rpc_block_count_interval : '' }}" :options="$intervals"/>
        <x-input.check name="active" label="Enable" :checked="isset($currency) && $currency->active" suffix="{{ isset($currency) ? $currency-> id : ''}}"/>              
    </x-form-column>
</x-form>