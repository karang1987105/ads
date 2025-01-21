<x-form name="add" method="POST">
    @isset($place)
        <x-slot name="action">
            {{ route($route . '.places.update', compact('place'), false) }}
        </x-slot>
        <x-slot name="method">PUT</x-slot>
        <x-slot name="submit">Ads.item.submitForm(this)</x-slot>
    @else
        <x-slot name="action">
            {{ route($route . '.places.store', Auth::user()->isManager() ? ['publisher' => $publisher] : [], false) }}
        </x-slot>
        <x-slot name="submit">Ads.list.submitAddForm(this)</x-slot>
    @endisset
    <x-form-column>
        <x-input.text name="title" label="Title" placeholder="Example: CPM Medium Rectangle"
		onfocus="this.placeholder=''" onblur="this.placeholder='Example: CPM Medium Rectangle'"
		value="{{ isset($place) ? $place->title : '' }}" required/>
    </x-form-column>
    <x-form-column>
        <x-input.select name="ad_type_id" label="AD Type"
                        value="{{ isset($place) ? $place->ad_type_id : '' }}"
                        :options="$adtype_options"/>
        <x-input.select name="domain_id" label="Domain"
                        value="{{ isset($place) ? $place->domain_id : '' }}"
                        :options="$domain_options"/>
        @if(Auth::user()->isManager())
            <x-input.check name="approve" label="Approve" :checked="isset($place) && $place->isApproved()"/>
        @endif
    </x-form-column>
</x-form>