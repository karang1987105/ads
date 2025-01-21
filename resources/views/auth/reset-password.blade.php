<x-page-layout>
<x-slot name="page_title">Password Recovery</x-slot>
<div class="row justify-content-center">
<div class="card card-small col-md-6">
<div class="card-header border-bottom row">
<h5 class="col mb-0" style="text-align:center">Password Recovery</h5>
</div>
<ul class="list-group list-group-flush mb-1">
<li class="list-group-item">
@if ($errors->any())
<div class="mb-4 alert alert-danger rounded">
<div class="font-medium text-red-600" style="text-align:center; font-weight:bold; font-size:16px">Whoops, something went wrong!</div>
<ul class="mt-3 list-disc list-inside text-sm text-red-600">
@foreach ($errors->all() as $error)
<li>{{ $error }}</li>
@endforeach
</ul>
</div>
@endif
<form method="POST" action="{{ route('password.update') }}">
@csrf
<input type="hidden" name="token" value="{{ $request->route('token') }}">
<x-input.text name="email" label="Email Address" required icon="mail" center="true"
:value="old('email', $request->email)"/>
<x-input.password name="password" label="New Password" required icon="password" center="true"/>
<x-input.password name="password_confirmation" label="Confirm Password" required
center="true" icon="password"/>
<div class="form-group row mb-0">
<div class="col-md-8 offset-md-4">
<button type="submit" class="btn btn-primary">Reset Password</button>
</div>
</div>
</form>
</li>
</ul>
</div>
</div>
</x-page-layout>