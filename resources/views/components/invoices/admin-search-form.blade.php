<x-form-search action="{{ route('admin.invoices.list', ['user' => $user], false) }}">
    <x-form-column>
		@if ($user->isPublisher())	
        <x-input.select name="currency" default-option-caption="List All Currencies" data-size="5"
                        :options="$currencies_options"/>
		@else
		<x-input.select name="paid"
                        :options="[['value'=>'', 'caption'=>'Any'],['caption'=>'Yes','value'=>1],['caption'=>'No','value'=>0]]"/>
		@endIf
	</x-form-column>
	<x-form-column>
		@if ($user->isPublisher())		
        <x-input.select name="paid"
                        :options="[['value'=>'', 'caption'=>'Any'],['caption'=>'Yes','value'=>1],['caption'=>'No','value'=>0]]"/>
		@endIf
	</x-form-column>
	<x-form-column>							
        <x-input.text name="amount_gt" label="Amount Greater Than"/>
        <x-input.text name="amount_lt" label="Amount Lower Than"/>
	</x-form-column>
	<x-form-column>			
        <x-input.text name="issue_after" label="Issue After" class="datepicker-field"/>
        <x-input.text name="issue_before" label="Issue Before" class="datepicker-field"/>
        @if ($user->isPublisher())
        <x-input.check name="withdrawal" label="Withdrawal Request"/>
        @endIf
    </x-form-column>
</x-form-search>
