<x-form name="add" method="POST">
    <x-slot name="action">
        {{ route('admin.invoices.store', ['user' => $user->id],false) }}
    </x-slot>
    <x-slot name="submit">Ads.list.submitAddForm(this)</x-slot>
    <x-form-column>
        <x-input.text name="amount"
                      description="{!!'Positive amount to add and negative amount to remove balance.'.($user->isAdvertiser()?' Removable amount: '.$minimum.'':'')!!}"/>
    </x-form-column>
    <x-form-column>
        <x-input.text name="title" label="Comment" required />
    </x-form-column>
</x-form>