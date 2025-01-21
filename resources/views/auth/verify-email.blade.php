<x-page-layout>
    <x-slot name="page_title">Verify Email Address</x-slot>
    <div class="row justify-content-center">
        <div class="card card-small col-md-6">
            <div class="card-header border-bottom row">
                <h5 class="col mb-0">Verify Email Address</h5>
                <a class="col pb-0 mb-0 mt-1 text-right" style="font-weight:bold" href="{{ route('logout') }}">Logout</a>
            </div>
            <ul class="list-group list-group-flush mb-1">
                <li class="list-group-item">
                    @if (session('status') == 'verification-link-sent')
                    <div class="mb-4 font-medium text-sm text-green-600">
                    A new verification link has been sent to the email address you provided during
                    registration.
                    </div>
                    @endif
                    <div class="mb-4 font-medium text-sm text-green-600">
                    <h4>Thank you for signing up!</h4>
                    We have sent an email to the email address you provided during registration. Please click on the verification link to activate your account.
                    If you don't receive any email from us during the next 30 minutes please click the button below to send another one. Check also your
                    spam folder just to be sure.</br></br>
                    If none of our emails ever reach you please <a href="{{ route('contact.create') }}">contact our support</a> and we will help you!                    
                    </div>
                    <form method="POST" action="{{ route('verification.send') }}">
                        @csrf
                        <div class="form-group row mb-0">
                            <div class="col-md-8 offset-md-4">
                                <button type="submit" class="btn btn-primary">Resend Verification Email</button>
                            </div>
                        </div>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</x-page-layout>
