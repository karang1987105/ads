<x-form-search action="{{ route('admin.ads.list', ['advertiser' => $advertiser->user_id], false) }}">
    <x-form-column>
        <x-input.select name="ad_type" data-size="10" label="AD Type"
                        :options="$adtype_options"/>
    </x-form-column>
    <x-form-column>
        <x-input.text name="title" lavel="Title"/>
        <x-input.select name="approved"
                        :options="[['value'=>'', 'caption'=>'Any'],['caption'=>'Yes','value'=>1],['caption'=>'No','value'=>0]]"/>
    </x-form-column>
</x-form-search>
