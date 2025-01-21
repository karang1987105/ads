<x-form-search action="{{ route('admin.currencies.list', absolute: false) }}">
    <x-form-column>
        <x-input.text name="id" label="Currency ID"/>
    </x-form-column>
    <x-form-column>
        <x-input.text name="name"/>
        <x-input.select name="active"
                        :options="[['value'=>'', 'caption'=>'Any'],['value'=>1,'caption'=>'Yes'],['value'=>0,'caption'=>'No']]"/>
    </x-form-column>
</x-form-search>
