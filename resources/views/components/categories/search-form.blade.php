<x-form-search action="{{ route('admin.categories.list', absolute: false) }}">
    <x-form-column>
        <x-input.text name="title"/>
    </x-form-column>
    <x-form-column>
        <x-input.select name="active" label="Public"
                        :options="[['value'=>'', 'caption'=>'Any'],['value'=>1,'caption'=>'Yes'],['value'=>0,'caption'=>'No']]"/>
    </x-form-column>
</x-form-search>
