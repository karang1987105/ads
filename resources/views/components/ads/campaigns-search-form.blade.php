<x-form-search action="{{ route('admin.ads.campaigns.list', ['ad' => $ad->id], false) }}">
    <x-form-column>
        <x-input.select name="category" label="Category" data-size="10"
                        :options="$category_options"/>
    </x-form-column>
    <x-form-column>
        <x-input.select name="device"
                        :options="[['value'=>'','caption'=>'All'],['value'=>'Mobile'],['value'=>'Desktop']]"/>
        <x-input.select name="active"
                        :options="[['value'=>'','caption'=>'Any'],['value'=>'1','caption'=>'Yes'],['value'=>'0','caption'=>'No']]"/>
    </x-form-column>
</x-form-search>
