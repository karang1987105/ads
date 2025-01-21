<x-form name="pay" method="POST">
    <x-slot name="action">
        {{ route('admin.withdrawals.withdrawal', $withdrawal, false) }}
    </x-slot>
    <x-slot name="submit">Ads.list.submitForm(this, 'PUT', () => Ads.list.refresh(this), true)</x-slot>
    <x-form-column>
        <x-input.text name="txid" label="Transaction ID" value="" required/>
    </x-form-column>
</x-form>