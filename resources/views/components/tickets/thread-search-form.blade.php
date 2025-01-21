<x-form-search action="{{ route('tickets.threads.list', ['key'=>$key], false) }}">
@userType('manager')
    <x-form-column>
        <x-input.select name="user" default-option-caption="List All Users" data-live-search="true" data-size="5"
                        :options="$users_options"/>
    </x-form-column>
@endUserType
@userType('manager')
    <x-form-column>
        <x-input.text name="guest" label="Guest Email"/>
    </x-form-column>
@endUserType
    <x-form-column>
        <x-input.text name="subject"/>
        </x-form-column>
        <x-form-column>
        <x-input.text name="message" label="Messages Contents"/>
    </x-form-column>
    <x-form-column>
        <x-input.text name="date_before" label="Date Before" class="datepicker-field"/>
        <x-input.text name="date_after" label="Date After" class="datepicker-field"/>
    </x-form-column>
@userType('publisher')    
    <x-form-column>
        <x-input.select name="category"
                        :options="[['value'=>'', 'caption'=>'List All Categories'],['value'=>'Publishers'],['value'=>'Billing'],['value'=>'Other']]"/>
    </x-form-column>
@endUserType
@userType('advertiser')    
    <x-form-column>
        <x-input.select name="category"
                        :options="[['value'=>'', 'caption'=>'List All Categories'],['value'=>'Advertisers'],['value'=>'Billing'],['value'=>'Other']]"/>
    </x-form-column>
@endUserType   
</x-form-search>