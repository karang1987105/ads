<x-form-search action="{{ route('admin.promos.list',  absolute: false) }}">
    <x-form-column>
        <x-input.text name="title" label="Title" />
    </x-form-column>
    <x-form-column>
        <x-input.text name="code" label="Promo Code" />
    </x-form-column>
</x-form-search>
