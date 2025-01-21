<x-form-search action="{{ route('advertiser.invoices.list', ['key' => $key], false) }}">
    <x-form-column>
        <x-input.text name="amount_gt" label="Amount Greater Than"/>
    </x-form-column>
    <x-form-column>
        <x-input.text name="amount_lt" label="Amount Lower Than"/>
    </x-form-column>
    <x-form-column>
        <x-input.text name="issue_before" label="Issue Before" class="datepicker-field"/>
    </x-form-column>
    <x-form-column>
        <x-input.text name="issue_after" label="Issue After" class="datepicker-field"/>
    </x-form-column>
</x-form-search>
