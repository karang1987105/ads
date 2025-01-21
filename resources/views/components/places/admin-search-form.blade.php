<x-form-search action="{{ route('admin.places.publishers-list', ['key' => $key], false) }}">
    <x-form-column>
        <x-input.select name="user" label="Owner" default-option-caption="List All Owners" data-live-search="true" data-size="5"
                        :options="$users_options"/>
        <x-input.text name="title"/>
    </x-form-column>
    <x-form-column>
        <x-input.select name="ad_type" label="AD Type" default-option-caption="List All Types" data-size="5"
                        :options="$adtypes_options"/>
        <x-input.text name="domain"/>
    </x-form-column>
</x-form-search>
