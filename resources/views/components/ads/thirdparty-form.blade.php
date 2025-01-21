<x-form name="add" method="POST" enctype="multipart/form-data">
    @isset($ad)
        <x-slot name="action">{{ route('admin.ads.update', ['ad' => $ad], false) }}</x-slot>
        <x-slot name="method">PUT</x-slot>
        <x-slot name="submit">Ads.item.submitForm(this)</x-slot>
    @else
        <x-slot name="action">{{ route('admin.ads.store', absolute: false) }}</x-slot>
        <x-slot name="submit">Ads.list.submitAddForm(this)</x-slot>
    @endisset
    <x-form-column>
        <x-input.select name="ad_type_id" label="AD Type" data-size="10"
        :options="$adtype_options" value="{{ isset($ad) ? $ad->ad_type_id : '' }}"/>
    </x-form-column>
		<x-form-column>
        <x-input.text name="thirdparty[title]" value="{{ isset($ad, $ad->thirdParty) ? $ad->thirdParty->title : '' }}"
                      required label="Title"
					  placeholder="Example: Third Party AD"
					  onfocus="this.placeholder=''"
					  onblur="this.placeholder='Example: Third Party AD'"/>
	</x-form-column>

	<x-form-column>
	<x-input.textarea name="thirdparty[code]"
                      value="{!! isset($ad, $ad->thirdParty) ? htmlentities($ad->thirdParty->code) : '' !!}"
                      required label="AD Code" data-ad-type="Third Party" style="height: 200px"/>
	</x-form-column>	

    <input type="hidden" name="third_party" value="1"/>
</x-form>
