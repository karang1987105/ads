<x-form-search action="{{ route('admin.managers.list', ['key' => $key], false) }}">
    <x-form-column>
        <x-input.text name="name"/>
        <x-input.text name="email" label="Email Address"/>
        <x-input.select name="active"
                        :options="[['value'=>'', 'caption'=>'Any'],['caption'=>'Yes','value'=>1],['caption'=>'No','value'=>0]]"/>
        <x-input.select name="email_verified" label="Email Verified"
                        :options="[['value'=>'', 'caption'=>'Any'],['caption'=>'Yes','value'=>1],['caption'=>'No','value'=>0]]"/>
    </x-form-column>
    <x-form-column>
        <x-input.text name="company"/>
        <x-input.select name="country_id" label="Country" default-option-caption="List All Countries" data-live-search="true" data-size="5"
                        :options="$country_options"/>
    </x-form-column>
</x-form-search>
