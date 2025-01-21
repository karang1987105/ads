<x-form-search action="{{ route('admin.ad-types.list', absolute: false) }}">
    <x-form-column>
        <x-input.text name="name"/>
    </x-form-column>
    <x-form-column>
        <x-input.select name="type"
                        :options="[['value'=>'', 'caption'=>'Any'],['value'=>'Banner'],['value'=>'Video']]"/>
    </x-form-column>
    <x-form-column>
        <x-input.select name="device"
                        :options="[['value'=>'', 'caption'=>'All'],['value'=>'Desktop'],['value'=>'Mobile']]"/>
    </x-form-column>
    <x-form-column>
        <x-input.select name="active"
                        :options="[['value'=>'', 'caption'=>'Any'],['value'=>1,'caption'=>'Yes'],['value'=>0,'caption'=>'No']]"/>
    </x-form-column>
</x-form-search>
