@if (request()->has('suspended'))
    <x-page-layout>
        <x-slot name="page_title">Account Suspended</x-slot>
        <div class="row justify-content-center">
        <div class="card card-small col-md-6">
        <div class="card-header border-bottom row">
        <h5 class="col mb-0" style="text-align:center">Account Suspended</h5>
        </div>
        <ul class="list-group list-group-flush mb-1">
        <li class="list-group-item">
        <div class="mb-4 font-medium text-sm"
        style="text-align:center; font-weight:bold; font-size:18px; color:red">
        Your account has been suspended!</br>
        </div>
        <div style="text-align:center">
        You may login to your account and submit a ticket for more info.</br></br>
        </div>
        <div class="text-center">
        <a class="btn btn-primary" href="{{ route('dashboard') }}">Login</a>
        </div>
        </li>
        </ul>
        </div>
        </div>
    </x-page-layout>
@elseif (request()->has('something'))
@else
    <x-page-layout title=''>
        <div class="container">
        </div>
    </x-page-layout>
@endif