<x-form name="add" method="POST">
    @isset($manager)
        <x-slot name="action">{{ route('admin.managers.update', compact('manager'), false) }}</x-slot>
        <x-slot name="method">PUT</x-slot>
        <x-slot name="submit">Ads.item.submitForm(this)</x-slot>
    @else
        <x-slot name="action">{{ route('admin.managers.store', absolute: false) }}</x-slot>
        <x-slot name="submit">Ads.list.submitAddForm(this)</x-slot>
    @endisset
    <x-form-column>
        <x-input.text name="name" icon="person" required value="{{isset($manager) ? $manager->user->name : ''}}"/>
        <x-input.text name="email" label="Email Address" icon="mail" required
                      value="{{isset($manager) ? $manager->user->email : ''}}"/>
        <x-input.select name="country_id" label="Country"
                        value="{{isset($manager) ? $manager->user->country_id : ''}}"
                        :required="config('ads.managers.required_fields.country_id')"
                        data-live-search="true" data-size="5"
                        :options="$country_options"/>
        <x-input.password name="password" icon="password" :required="!isset($manager)"/>
        <x-input.password name="password_confirmation" label="Confirm Password" icon="password" :required="!isset($manager)"
						  description="Must contain at least eight characters. One uppercase letter, one digit and one special sign!"/>
        <x-input.check name="active" label="Approve" :checked="isset($manager)&&$manager->user->active" suffix="{{ isset($manager) ? $manager->user->id : '' }}"/>
        <x-input.check name="email_verified" label="Email Verified"
                       :checked="isset($manager)&&$manager->user->email_verified_at" suffix="{{ isset($manager) ? $manager->user->id : '' }}"/>
	</x-form-column>
	<x-form-column>
        <x-input.text name="address" value="{{isset($manager) ? $manager->user->address : ''}}"
                      :required="config('ads.managers.required_fields.address')"/>	
        <x-input.text name="state" value="{{isset($manager) ? $manager->user->state : ''}}"
                      :required="config('ads.managers.required_fields.state')"/>
        <x-input.text name="city" value="{{isset($manager) ? $manager->user->city : ''}}"
                      :required="config('ads.managers.required_fields.city')"/>
        <x-input.text name="zip" label="Zip Code" value="{{isset($manager) ? $manager->user->zip : ''}}"
                      :required="config('ads.managers.required_fields.zip')"/>
        <x-input.text name="company" value="{{isset($manager) ? $manager->user->company : ''}}"
                      :required="config('ads.managers.required_fields.company')"/>
        <x-input.text name="business_id" label="Business ID" 
                      :required="config('ads.managers.required_fields.business_id')"
                      value="{{isset($manager) ? $manager->user->business_id : ''}}"/>
        <x-input.text name="phone" value="{{isset($manager) ? $manager->user->phone : ''}}"
                      :required="config('ads.managers.required_fields.phone')"/>
    </x-form-column>

    <fieldset class="row col-12">
        <div class="legend">
            <label>Notifications</label>
        </div>
        <div class="col-6 col-md-4 col-lg-3">
            <x-input.check name="notifications[Account]" label="Account Updates"
                           :checked="isset($manager) ? isset($manager->user->notifications) && in_array('Account', $manager->user->notifications) : true"
                           suffix="{{ isset($manager) ? $manager->user->id : '' }}"/>
        </div>
    </fieldset>
</x-form>
