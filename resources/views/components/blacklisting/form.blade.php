<x-form name="add" method="POST">
    @isset($item)
        <x-slot name="action">{{ route('admin.blacklisting.update', $item, false) }}</x-slot>
        <x-slot name="method">PUT</x-slot>
        <x-slot name="submit">Ads.item.submitForm(this)</x-slot>
    @else
        <x-slot name="action">{{ route('admin.blacklisting.store', absolute: false) }}</x-slot>
        <x-slot name="submit">Ads.list.submitAddForm(this)</x-slot>
    @endisset
    <x-form-column>
        <x-input.text name="domain" value="{{ isset($item) ? $item->domain : '' }}"
		description="Domain name without schema and path. Example: domain.tld, sub.domain.tld."/>
    </x-form-column>
</x-form>
