<x-page-layout>
    <x-slot name="page_title">Contact Us</x-slot>
    <div class="row justify-content-center pt-5 pb-4">
        <div class="card card-small col-md-6">
            <div class="card-header border-bottom row">
                <h5 class="col mb-0" style="text-align:center">Contact Us</h5>
            </div>
            <ul class="list-group list-group-flush mb-1">
                <li class="list-group-item">
                    @if(Session::has('success'))
                    <div class="alert alert-success">
                    {{ Session::get('success') }}
                    </div>
                    @endif
                    <form method="POST" action="{{ route('contact.send') }}">
                    @csrf
                    @if ($errors->any())
                    <div class="mb-4 alert alert-danger rounded">
                    <div class="font-medium text-red-600" style="text-align:center; font-weight:bold; font-size:16px">Whoops, something went wrong!</div></br>                         
                    @foreach ($errors->all() as $error)
                    <ul><li>{{ $error }}</li></ul>
                    @endforeach
                    </div>
                    @endif
                    <x-input.text name="email" label="Email Address" icon="mail" required value="{{ old('email') }}" center="true"/>
                        <div class="form-group row">
                            <label class="col-md-4 col-form-label text-md-right">Category</label>
                            <div data-toggle="buttons" class="col-md-6 btn-group btn-group-toggle">
                                <label class="btn btn-white">
                                    <input type="radio" name="category" id="category" value="Advertisers"
                                           autocomplete="off"
                                           required {{ old('category')=='Advertisers' ? 'checked' : ''}}>
                                    Advertisers
                                </label>
                                <label class="btn btn-white">
                                    <input type="radio" name="category" id="category" value="Publishers"
                                           autocomplete="off"
                                           required {{ old('category')=='Publishers' ? 'checked' : ''}}>
                                    Publishers
                                </label>
                                <label class="btn btn-white">
                                    <input type="radio" name="category" id="category" value="Billing" autocomplete="off"
                                           required {{ old('category')=='Billing' ? 'checked' : ''}}>
                                    Billing
                                </label>
                                <label class="btn btn-white">
                                    <input type="radio" name="category" id="category" value="Other" autocomplete="off"
                                           required {{ old('category')=='Other' ? 'checked' : ''}}>
                                    Other
                                </label>
                            </div>
                        </div>
                        <x-input.text name="subject" icon="subtitles" value="{{ old('subject') }}" center="true" required/>
                        <x-input.textarea name="message" value="{{ old('message') }}" center="true" required rows="10"/>
                        <div class="form-group row">
                            <div onclick="Ads.Modules.Captcha.reload(this)" class="captcha col-md-4 text-md-right"
                                 title="Reload">
                                {!! captcha_img() !!}
                            </div>
                            <div class="col-md-6 input-group">
                                <input class="form-control {{ $errors->has('captcha') ? 'is-invalid' : '' }}"
                                       type="text" name="captcha" placeholder="Enter Captcha"/>
                                @error('captcha')
                                <span class="invalid-feedback" role="alert">Invalid Captcha</span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row mb-0">
                            <div class="col-md-8 offset-md-4">
                                <button type="submit" class="btn btn-primary">Send Message</button>
                            </div>
                        </div>
                    </form>
                </li>
            </ul>
        </div>
    </div>
    <div class="pt-4"></div>
</x-page-layout>
