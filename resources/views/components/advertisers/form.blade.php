<x-form name="add" method="POST">
    @isset($advertiser)
        <x-slot name="action">{{ route('admin.advertisers.update', compact('advertiser'), false) }}</x-slot>
        <x-slot name="method">PUT</x-slot>
        <x-slot name="submit">Ads.item.submitForm(this)</x-slot>
    @else
        <x-slot name="action">{{ route('admin.advertisers.store', absolute: false) }}</x-slot>
        <x-slot name="submit">Ads.list.submitAddForm(this)</x-slot>
    @endisset
    <x-form-column>
        <x-input.text name="name" icon="person" required value="{{isset($advertiser) ? $advertiser->user->name : ''}}"/>
        <x-input.text name="email" label="Email Address" icon="mail" required
                      value="{{isset($advertiser) ? $advertiser->user->email : ''}}"/>
        <x-input.select name="country_id" label="Country"
                        value="{{isset($advertiser) ? $advertiser->user->country_id : ''}}"
                        :required="config('ads.advertisers.required_fields.country_id')"
                        data-live-search="true" data-size="5"
                        :options="$country_options"/>					  
        <x-input.password name="password" icon="password" :required="!isset($advertiser)"/>
        <x-input.password name="password_confirmation" label="Confirm Password" icon="password" :required="!isset($advertiser)"
		description="Must contain at least eight characters. One uppercase letter, one digit and one special sign!"/>
        <x-input.check name="active" label="Approve" :checked="isset($advertiser)&&$advertiser->user->active" suffix="{{ isset($advertiser) ? $advertiser->user->id : '' }}"/>
        <x-input.check name="email_verified" label="Email Verified"
                       :checked="isset($advertiser)&&$advertiser->user->email_verified_at" suffix="{{ isset($advertiser) ? $advertiser->user->id : '' }}"/>
	</x-form-column>
	<x-form-column>
		<x-input.text name="address" value="{{isset($advertiser) ? $advertiser->user->address : ''}}"
                      :required="config('ads.advertisers.required_fields.address')"/>
        <x-input.text name="state" value="{{isset($advertiser) ? $advertiser->user->state : ''}}"
                      :required="config('ads.advertisers.required_fields.state')"/>
        <x-input.text name="city" value="{{isset($advertiser) ? $advertiser->user->city : ''}}"
                      :required="config('ads.advertisers.required_fields.city')"/>
        <x-input.text name="zip" label="Zip Code" value="{{isset($advertiser) ? $advertiser->user->zip : ''}}"
                      :required="config('ads.advertisers.required_fields.zip')"/>					  
        <x-input.text name="company" value="{{isset($advertiser) ? $advertiser->user->company : ''}}"
                      :required="config('ads.advertisers.required_fields.company')"/>
        <x-input.text name="business_id" label="Business ID"
                      :required="config('ads.advertisers.required_fields.business_id')"
                      value="{{isset($advertiser) ? $advertiser->user->business_id : ''}}"/>
        <x-input.text name="phone" value="{{isset($advertiser) ? $advertiser->user->phone : ''}}"
                      :required="config('ads.advertisers.required_fields.phone')"/>
    </x-form-column>

    <fieldset class="row col-12">
        <div class="legend">
            <label>Notifications</label>
        </div>
        <div class="col-6 col-md-4 col-lg-3">
            <x-input.check name="notifications[Account]" label="Account Updates"
                           :checked="isset($advertiser) ? isset($advertiser->user->notifications) && in_array('Account', $advertiser->user->notifications) : true"
                           suffix="{{ isset($advertiser) ? $advertiser->user->id : '' }}"/>
        </div>
        <div class="col-6 col-md-4 col-lg-3">
            <x-input.check name="notifications[Domain]" label="Domains Updates"
                           :checked="isset($advertiser) ? isset($advertiser->user->notifications) && in_array('Domain', $advertiser->user->notifications) : true"
                           suffix="{{ isset($advertiser) ? $advertiser->user->id : '' }}"/>
        </div>
        <div class="col-6 col-md-4 col-lg-3">
            <x-input.check name="notifications[Advertisement]" label="ADS Updates"
                           :checked="isset($advertiser) ? isset($advertiser->user->notifications) && in_array('Advertisement', $advertiser->user->notifications) : true"
                           suffix="{{ isset($advertiser) ? $advertiser->user->id : '' }}"/>
        </div>
        <div class="col-6 col-md-4 col-lg-3">
            <x-input.check name="notifications[Campaign]" label="Campaigns Updates"
                           :checked="isset($advertiser) ? isset($advertiser->user->notifications) && in_array('Campaign', $advertiser->user->notifications) : true"
                           suffix="{{ isset($advertiser) ? $advertiser->user->id : '' }}"/>
        </div>
    </fieldset>
</x-form>
