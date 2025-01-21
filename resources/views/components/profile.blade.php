<x-form name="update" method="POST" class="d-flex">
    <x-slot name="action">{{ route('profile.update', absolute: false) }}</x-slot>
    <x-slot name="method">PUT</x-slot>
    <x-slot name="submit">Ads.Modules.Profile.submitForm(this)</x-slot>
    <x-form-column>
        <x-input.text name="name" label="Full Name" icon="person" value="{{ old('name') ?? $user->name }}"/>
        <x-input.text name="email" label="Email Address" icon="mails" value="{{ old('email') ?? $user->email }}" :disabled="!$user->isAdmin()"/>
		<x-input.select name="country_id" label="Country"
                        value="{{ old('country_id') ?? $user->country_id }}" :options="$country_options"/>        
		<x-input.password name="password" icon="password"/>
        <x-input.password name="password_confirmation" label="Confirm Password" icon="password"
						  description="Must contain at least eight characters. One uppercase letter, one digit and one special sign!"/>
    </x-form-column>
    <x-form-column>
	    <x-input.text name="address" value="{{ old('address') ?? $user->address }}"/>
		<x-input.text name="state" value="{{ old('state') ?? $user->state }}"/>
        <x-input.text name="city" value="{{ old('city') ?? $user->city }}"/>
        <x-input.text name="zip" label="Zip Code" value="{{ old('zip') ?? $user->zip }}"/>
        <x-input.text name="company" value="{{ old('company') ?? $user->company }}"/>
        <x-input.text name="business_id" label="Business ID" value="{{ old('business_id') ?? $user->business_id }}"/>
        <x-input.text name="phone" value="{{ old('phone') ?? $user->phone }}"/>
    </x-form-column>
    @if(!$user->isAdmin())
        <fieldset class="row col-12">
            <div class="legend">
                <label>Notifications</label>
            </div>
            @if($user->isPublisher())
                <div class="col-6 col-md-4 col-lg-3">
                    <x-input.check name="notifications[Account]" label="Account Updates"
                                   :checked="old('notifications.Account') || in_array('Account', $user->notifications)"/>
                </div>
                <div class="col-6 col-md-4 col-lg-3">
                    <x-input.check name="notifications[Domain]" label="Domains Updates"
                                   :checked="old('notifications.Domain') || in_array('Domain', $user->notifications)"/>
                </div>
                <div class="col-6 col-md-4 col-lg-3">
                    <x-input.check name="notifications[Place]" label="Places Updates"
                                   :checked="old('notifications.Place') || in_array('Place', $user->notifications)"/>
                </div>
            @elseif($user->isAdvertiser())
                <div class="col-6 col-md-4 col-lg-3">
                    <x-input.check name="notifications[Account]" label="Account Updates"
                                   :checked="old('notifications.Account') || in_array('Account', $user->notifications)"/>
                </div>
                <div class="col-6 col-md-4 col-lg-3">
                    <x-input.check name="notifications[Domain]" label="Domains Updates"
                                   :checked="old('notifications.Domain') || in_array('Domain', $user->notifications)"/>
                </div>
                <div class="col-6 col-md-4 col-lg-3">
                    <x-input.check name="notifications[Advertisement]" label="ADS Updates"
                                   :checked="old('notifications.Advertisement') || in_array('Advertisement', $user->notifications)"/>
                </div>
                <div class="col-6 col-md-4 col-lg-3">
                    <x-input.check name="notifications[Campaign]" label="Campaigns Updates"
                                   :checked="old('notifications.Campaign') || in_array('Campaign', $user->notifications)"/>
                </div>
            @elseif($user->isManager() && !$user->isAdmin())
                <div class="col-6 col-md-4 col-lg-3">
                    <x-input.check name="notifications[Account]" label="Account Updates"
                                   :checked="old('notifications.Account') || in_array('Account', $user->notifications)"/>
                </div>
            @endif
        </fieldset>
    @endif
</x-form>
