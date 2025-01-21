<x-page-layout>
    <x-slot name="page_title">Registration</x-slot>
    <div class="row justify-content-center pb-4" style="color: black">
        <div class="card card-small col-md-6">
            <div class="card-header border-bottom row">
                <h5 class="col mb-0" style="text-align:center">Registration</h5>
            </div>
            <ul class="list-group list-group-flush mb-1">
                <li class="list-group-item">
                    <form method="POST" action="{{ route('register') }}">
                        @csrf
                        @if ($errors->any())
                        <div class="mb-4 alert alert-danger rounded">
                        <div class="font-medium text-red-600" style="text-align:center; font-weight:bold; font-size:16px">Whoops, something went wrong!</div></br>                         
                        @foreach ($errors->all() as $error)
                        <ul><li>{{ $error }}</li></ul>
                        @endforeach
                        </div>
                        @endif
                        <div class="form-group row">
                            <label class="col-md-4 col-form-label text-md-right">Account Type <a class="bold red">*</a></label>
                            <div data-toggle="buttons" class="col-md-6 btn-group btn-group-toggle mb-3">
                                <label class="btn btn-white">
                                    <input type="radio" name="type" id="type" value="Advertiser" autocomplete="off"
                                           required {{ old('type')=='Advertiser' ? 'checked' : ''}}>
                                    Advertiser
                                </label>
                                <label class="btn btn-white">
                                    <input type="radio" name="type" id="type" value="Publisher" autocomplete="off"
                                           required {{ old('type')=='Publisher' ? 'checked' : ''}}>
                                    Publisher
                                </label>
                            </div>
                        </div>

                        <x-input.text name="name" label="Full Name <a style='color:red; font-weight:bold'>*</a>" icon="person" required value="{{ old('name') }}" center="true"/>
                        <x-input.text name="email" label="Email Address <a style='color:red; font-weight:bold'>*</a>" icon="mail" required value="{{ old('email') }}"
                                        center="true"/>
                        <x-input.password name="password" label="Password <a style='color:red; font-weight:bold'>*</a>" icon="password" required center="true"/>
                        <x-input.password name="password_confirmation" label="Confirm Password <a style='color:red; font-weight:bold'>*</a>"
                                          icon="password" required center="true"/><hr/>                  
                        <x-input.select name="country_id" value="{{ old('country_id') }}" center="true" label="Country <a style='color:red; font-weight:bold'>*</a>"
                                        data-live-search="true" data-size="5"
                                        :options="$country_options"/>
                        <x-input.text name="state" value="{{ old('state') }}" center="true"/>
                        <x-input.text name="city" value="{{ old('city') }}" center="true"/>
                        <x-input.text name="zip" label="Zip Code" value="{{ old('zip') }}" center="true"/>
                        <x-input.text name="address" value="{{ old('address') }}" center="true"/>
                        <hr/>
                        <x-input.text name="company" value="{{ old('company') }}" center="true"/>
                        <x-input.text name="phone" value="{{ old('phone') }}" center="true"/>
                        <x-input.text name="business_id" label="Business ID" value="{{ old('business_id') }}"
                                      center="true"/>
                        <div class="form-group row">
                            <div onclick="Ads.Modules.Captcha.reload(this)" class="captcha col-md-4 text-md-right"
                                 title="Reload">
                                {!! captcha_img() !!}
                            </div>
                            <div class="col-md-6 input-group">
                                <input class="form-control {{ $errors->has('captcha') ? 'is-invalid' : '' }}"
                                    type="text" name="captcha"
                                    placeholder="Enter Captcha"
                                    onfocus="this.placeholder=''" 
                                    onblur="this.placeholder='Enter Captcha'"/>
                                @error('captcha')
                                <span class="invalid-feedback" role="alert">Invalid Captcha</span>
                                @enderror
                            </div>
                        </div>
                        <x-input.check name="tos" center="true" label="I agree to <a href={{ route('terms-of-service') }}>Terms Of Service!</a>"/>
                        <hr/>
                        <span>Please note that all fields marked with
                        <span style="color:red; font-weight:bold">*</span> are mandatory!</span>
                        <button type="submit" class="btn btn-primary" style="margin-left:45%; margin-top:3%">Register</button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
    <div class="pt-4"></div>
</x-page-layout>
