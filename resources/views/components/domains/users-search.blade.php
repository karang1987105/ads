<x-form-search action="{{ route('admin.domains.listUsers', ['key' => $key], false) }}">
    <x-form-column>
        <x-input.select name="user" label="Owner" default-option-caption="List All Users" data-live-search="true" data-size="5"
                        :options="$users_options"/>
    </x-form-column>
    <x-form-column>
        <x-input.text name="domain"/>
        @if($key === 'publishers')
        <x-input.select name="category" Label="Category" default-option-caption="List All Domains" data-size="5"
                        :options="$category_options"/>
        @endif
    </x-form-column>
</x-form-search>