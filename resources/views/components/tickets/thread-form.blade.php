<x-form name="add" method="POST">
    <x-slot name="action">{{ route('tickets.threads.store', absolute: false) }}</x-slot>
    <x-slot name="submit">Ads.list.submitAddForm(this)</x-slot>
    <x-form-column>
        <x-input.text name="subject" required/>
    </x-form-column>
@userType('manager')    
    <x-form-column>
    <x-input.select name="category" required
                    :options="[['value'=>'', 'caption'=>'Select Category'], ['value'=>'Advertisers'], ['value'=>'Publishers'], ['value'=>'Billing'], ['value'=>'Other']]"/>
    </x-form-column>
@endUserType    
@userType('publisher')    
    <x-form-column>
    <x-input.select name="category" required
                    :options="[['value'=>'', 'caption'=>'Select Category'], ['value'=>'Publishers'], ['value'=>'Billing'], ['value'=>'Other']]"/>
    </x-form-column>
@endUserType
@userType('advertiser')    
    <x-form-column>
    <x-input.select name="category" required 
                    :options="[['value'=>'', 'caption'=>'Select Category'], ['value'=>'Advertisers'], ['value'=>'Billing'], ['value'=>'Other']]"/>
    </x-form-column>
@endUserType    
    <x-form-column type="full">
    <x-input.textarea name="message" required rows="10"/>
    </x-form-column>
</x-form>
