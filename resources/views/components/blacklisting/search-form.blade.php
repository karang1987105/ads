<x-form-search action="{{ route('admin.blacklisting.list', absolute: false) }}">
    <x-form-column>
        <x-input.text name="domain"/>
    </x-form-column>
</x-form-search>
