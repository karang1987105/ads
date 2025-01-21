<x-form-search action="{{ route('records.list', ['key' => $key, 'category' => $category], false) }}">
    <x-form-column>
        <x-input.select label="Range" name="filter" :options="$options" value="{{ $filter }}" class="rel-parent" onchange="Ads.form.relations(this)"/>
        <x-input.text class="datepicker-field" label="From" name="from" value="{{ $from }}" data-filter="custom"/>
        <x-input.text class="datepicker-field" label="To" name="to" value="{{ $to }}" data-filter="custom"/>
    </x-form-column>
    @if(auth()->user()->isAdmin())
        <x-form-column>
            <x-input.select label="AD Type" default-option-caption="List All" name="filter_adtype" :options="$filter_adtype_options" value="{{ $filter_adtype }}"/>
            <x-input.select label="Category" default-option-caption="List All" name="filter_category" :options="$filter_category_options" value="{{ $filter_category }}"/>
        </x-form-column>
    @endif
</x-form-search>