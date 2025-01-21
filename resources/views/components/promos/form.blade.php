<x-form name="add" method="POST">
    @isset($promo)
        <x-slot name="action">{{ route('admin.promos.update', compact('promo'), false) }}</x-slot>
        <x-slot name="method">PUT</x-slot>
        <x-slot name="submit">Ads.item.submitForm(this)</x-slot>
    @else
        <x-slot name="action">{{ route('admin.promos.store', absolute: false) }}</x-slot>
        <x-slot name="submit">Ads.list.submitAddForm(this)</x-slot>
    @endisset
    <x-form-column>
        <x-input.text name="title" label="Title" required value="{{ isset($promo) ? $promo->title : '' }}"/>
        <x-input.text name="bonus" label="Promo Bonus" required value="{{ isset($promo) ? $promo->bonus : '' }}"
        description="Only numbers without special signs!" />
    </x-form-column>
    <x-form-column>    
        <x-input.text name="code" label="Promo Code" required value="{{ isset($promo) ? $promo->code : '' }}"/>
        <x-input.text name="total" label="Total Codes" value="{{ isset($promo) ? $promo->total : '' }}"/>
    </x-form-column>
</x-form>