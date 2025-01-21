<x-form-search action="{{ route('admin.ads.advertisers-list', ['key' => $key], false) }}">
    <x-form-column>
        <x-input.select name="ad_type" label="AD Type" default-option-caption="List All Types" data-size="10"
                        :options="$adtype_options"/>
    </x-form-column>
    <x-form-column>
        <x-input.text name="title"/>
    </x-form-column>
</x-form-search>
