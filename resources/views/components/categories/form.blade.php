<x-form name="add" method="POST">
    @isset($category)
        <x-slot name="action">{{ route('admin.categories.update', compact('category'), false) }}</x-slot>
        <x-slot name="method">PUT</x-slot>
        <x-slot name="submit">Ads.item.submitForm(this)</x-slot>
    @else
        <x-slot name="action">{{ route('admin.categories.store', absolute: false) }}</x-slot>
        <x-slot name="submit">Ads.list.submitAddForm(this)</x-slot>
    @endisset
    <x-form-column>
        <x-input.text name="title" value="{{ isset($category) ? $category->title : '' }}"/>
    </x-form-column>
    <x-form-column>
        <x-input.text name="cpc" label="CPC" value="{{ isset($category) ? round($category->cpc, 5) : '' }}"/>
        <x-input.text name="cpm" label="CPM" value="{{ isset($category) ? round($category->cpm, 5) : '' }}"/>		
        <x-input.text name="cpv" label="CPV" value="{{ isset($category) ? round($category->cpv, 5) : '' }}"/>
        <x-input.text name="revenue_share" label="Revenue Share %"
                      value="{{ isset($category) ? $category->revenue_share : '' }}"/>
        <x-input.check name="active" label="Public" :checked="isset($category) && $category->active" suffix="{{isset($category) ? $category-> id : ''}}"/>
    </x-form-column>
</x-form>
