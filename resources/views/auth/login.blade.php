<x-page-layout>
    <x-slot name="page_title">Login</x-slot>

    <div class="row justify-content-center" style="color: black">
        <div class="card card-small col-md-6">
            <div class="card-header border-bottom row">
                <h5 class="col mb-0">Login</h5>
                <a class="col pb-0 mb-0 mt-1 text-right" style="font-weight:bold" href="{{ route('register') }}">Register</a>
            </div>
            <ul class="list-group list-group-flush mb-1">
                <li class="list-group-item">
                    <form method="POST" action="{{ route('login') }}">
                        @csrf
                        @if ($errors->any())
                        <div class="mb-4 alert alert-danger rounded">
                        <div class="font-medium text-red-600" style="text-align:center; font-weight:bold; font-size:16px">Whoops, something went wrong!</div></br>                         
                        @foreach ($errors->all() as $error)
                        <ul><li>{{ $error }}</li></ul>
                        @endforeach
                        </div>
                        @endif
                        <x-input.text name="email" label="Email Address" required icon="person" center="true"/>
                        <x-input.password name="password" required icon="password" center="true"/>                        
                        @if(!isset($_COOKIE['gdpr']) || isset($_COOKIE['gdpr-functionality-cookies']))
                            <x-input.check name="remember" label="Remember Me" :checked="!!old('remember')"
                                           center="true"/>
                        @endif
                        <div class="form-group row mb-0">
                            <div class="col-md-8 offset-md-4">
                                <button type="submit" class="btn btn-primary">Login</button>
                                @if (Route::has('password.request'))
                                <a style="font-size:12px" href="{{ route('password.request') }}">Forgot Your Password?</a>
                                @endif
                            </div>
                        </div>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</x-page-layout>
