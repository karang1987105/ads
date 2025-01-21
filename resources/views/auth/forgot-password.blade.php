<x-page-layout>
    <x-slot name="page_title">Password Recovery</x-slot>
    <div class="row justify-content-center" style="color: black">
        <div class="card card-small col-md-6">
            <div class="card-header border-bottom row">
                <h5 class="col mb-0" style="text-align:center">Password Recovery</h5>
            </div>
            <ul class="list-group list-group-flush mb-1">
                <li class="list-group-item">
                @if (session('status'))
                <div class="mb-4">
                <div class="font-medium text-sm text-green-600"
                style="color:green; font-weight:bold; font-size:16px; text-align:center">{{ session('status') }}</div>
                </div>
                @endif
                @if ($errors->any())
                <div class="mb-4 alert alert-danger rounded">
                <div style="text-align:center; font-size:16px; font-weight:bold">Whoops, something went wrong!</div></br>
                @foreach ($errors->all() as $error)
                <ul><li>{{ $error }}</li></ul>
                @endforeach
                </div>
                @endif
                <form method="POST" action="{{ route('password.email') }}">
                @csrf
                <p class="text-muted mb-3">Forgot your password? No problem. Please enter the email address you registered with
                and we will send you a link that will allow you to reset your password. Please also check your spam folder just in case.</br></br>
                If you don't receive any email after the next 30 minutes please try again or <a href="{{ route('contact.create') }}">contact our support</a>
                and we will help you.</p>
                <x-input.text name="email" label="Email Address" required icon="mail" center="true"/>
                <div class="form-group row mb-0">
                <div class="col-md-8 offset-md-4">
                <button type="submit" class="btn btn-primary">Send Recovery Link</button>
                </div>
                </div>
                </form>
                </li>
            </ul>
        </div>
    </div>
</x-page-layout>
