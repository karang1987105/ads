<x-form name="add" method="POST">
    @isset($publisher)
        <x-slot name="action">{{ route('admin.publishers.update', compact('publisher'), false) }}</x-slot>
        <x-slot name="method">PUT</x-slot>
        <x-slot name="submit">Ads.item.submitForm(this)</x-slot>
    @else
        <x-slot name="action">{{ route('admin.publishers.store', absolute: false) }}</x-slot>
        <x-slot name="submit">Ads.list.submitAddForm(this)</x-slot>
    @endisset
    <x-form-column>
        <x-input.text name="name" icon="person" required value="{{isset($publisher) ? $publisher->user->name : ''}}"/>
        <x-input.text name="email" label="Email Address" icon="mail" required
                      value="{{isset($publisher) ? $publisher->user->email : ''}}"/>
        <x-input.select name="country_id" label="Country"
                        value="{{isset($publisher) ? $publisher->user->country_id : ''}}"
                        :required="config('ads.publishers.required_fields.country_id')"
                        data-live-search="true" data-size="5"
                        :options="$country_options"/>
        <x-input.password name="password" icon="password" :required="!isset($publisher)"/>
        <x-input.password name="password_confirmation" label="Confirm Password" icon="password" :required="!isset($publisher)"
						  description="Must contain at least eight characters. One uppercase letter, one digit and one special sign!"/>
        <x-input.check name="active" label="Approve" :checked="isset($publisher)&&$publisher->user->active" suffix="{{ isset($publisher) ? $publisher->user->id : '' }}"/>
        <x-input.check name="email_verified" label="Email Verified"
                       :checked="isset($publisher)&&$publisher->user->email_verified_at" suffix="{{ isset($publisher) ? $publisher->user->id : '' }}"/>
    </x-form-column>
    <x-form-column>
        <x-input.text name="address" value="{{isset($publisher) ? $publisher->user->address : ''}}"
                      :required="config('ads.publishers.required_fields.address')"/>
        <x-input.text name="state" value="{{isset($publisher) ? $publisher->user->state : ''}}"
                      :required="config('ads.publishers.required_fields.state')"/>
        <x-input.text name="city" value="{{isset($publisher) ? $publisher->user->city : ''}}"
                      :required="config('ads.publishers.required_fields.city')"/>
        <x-input.text name="zip" label="Zip Code" value="{{isset($publisher) ? $publisher->user->zip : ''}}"
                      :required="config('ads.publishers.required_fields.zip')"/>					  
        <x-input.text name="company" value="{{isset($publisher) ? $publisher->user->company : ''}}"
                      :required="config('ads.publishers.required_fields.company')"/>
        <x-input.text name="business_id" label="Business ID"
                      value="{{isset($publisher) ? $publisher->user->business_id : ''}}"
                      :required="config('ads.publishers.required_fields.business_id')"/>
        <x-input.text name="phone" value="{{isset($publisher) ? $publisher->user->phone : ''}}"
                      :required="config('ads.publishers.required_fields.phone')"/>
    </x-form-column>

    <fieldset class="row col-12">
        <div class="legend">
            <label>Notifications</label>
        </div>
        <div class="col-6 col-md-4 col-lg-3">
            <x-input.check name="notifications[Account]" label="Account Updates"
                           :checked="isset($publisher) ? isset($publisher->user->notifications) && in_array('Account', $publisher->user->notifications) : true"
                           suffix="{{ isset($publisher) ? $publisher->user->id : '' }}"/>
        </div>
        <div class="col-6 col-md-4 col-lg-3">
            <x-input.check name="notifications[Domain]" label="Domains Updates"
                           :checked="isset($publisher) ? isset($publisher->user->notifications) && in_array('Domain', $publisher->user->notifications) : true"
                           suffix="{{ isset($publisher) ? $publisher->user->id : '' }}"/>
        </div>
        <div class="col-6 col-md-4 col-lg-3">
            <x-input.check name="notifications[Place]" label="Places Updates"
                           :checked="isset($publisher) ? isset($publisher->user->notifications) && in_array('Place', $publisher->user->notifications) : true"
                           suffix="{{ isset($publisher) ? $publisher->user->id : '' }}"/>
        </div>
    </fieldset>
</x-form>
