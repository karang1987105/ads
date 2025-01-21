<x-form-search action="{{ route('admin.countries.list', compact('tier'), false) }}">
    <x-form-column>
		<x-input.text name="name" label="Country Name"/>
    </x-form-column>
	<x-form-column>
        <x-input.text name="id" label="Country ID"/>
    </x-form-column>
</x-form-search>
