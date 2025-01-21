<x-form-search action="{{ route('admin.invoices.listUsers', ['key' => $key], false) }}">
    <x-form-column>
        <x-input.select name="user" default-option-caption="List All Users" data-live-search="true" data-size="5"
                        :options="$users_options"/>
        @if($key === 'publishers')
            <x-input.check name="withdrawal" label="Withdrawal Requests"/>
        @endif
    </x-form-column>
</x-form-search>
