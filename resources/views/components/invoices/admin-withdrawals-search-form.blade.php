<x-form-search action="{{ route('admin.withdrawals.withdrawalsList', ['user' => $user], false) }}">
	<x-form-column>	
        <x-input.text name="id" label="Withdrawal ID"/>
		          <x-input.text name="issue_after" label="Issue After" class="datepicker-field"/>
        <x-input.text name="issue_before" label="Issue Before" class="datepicker-field"/>
	</x-form-column>	
	<x-form-column>	
        <x-input.select name="paid"
                        :options="[['value'=>'', 'caption'=>'Any'],['caption'=>'Yes','value'=>1],['caption'=>'No','value'=>0]]"/>			
        <x-input.select name="confirmed"
                        :options="[['value'=>'', 'caption'=>'Any'],['caption'=>'Yes','value'=>1],['caption'=>'No','value'=>0]]"/>
	</x-form-column>
</x-form-search>
