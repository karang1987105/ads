<x-form-search action="{{ route('advertiser.ads.list', absolute: false) }}">
    <x-form-column>
        <x-input.select name="ad_type" default-option-caption="" data-live-search="true" data-size="5" label="Ad Type"
                        :options="$adtype_options"/>
    </x-form-column>
    <x-form-column>
        <x-input.text name="ad_type_name" label="Ad Type Like" placeholder="Search by name"/>
        <x-input.text name="title"/>
    </x-form-column>
</x-form-search>
