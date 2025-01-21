<x-page-layout>
    <x-slot name="page_title">Confirm Password</x-slot>

    <div class="row justify-content-center">
        <div class="card card-small col-md-6">
            <div class="card-header border-bottom row">
                <h5 class="col mb-0">Password Confirmation</h5>
                <a class="col pb-0 mb-0 mt-1 text-right" href="{{ route('logout') }}">Logout</a>
            </div>
            <ul class="list-group list-group-flush mb-1">
                <li class="list-group-item">
                    @if ($errors->any())
                        <div class="mb-4 alert alert-danger rounded">
                            <div class="font-medium text-red-600">Whoops! Something went wrong.</div>
                            <ul class="mt-3 list-disc list-inside text-sm text-red-600">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <div class="mb-4 font-medium text-sm text-green-600">
                        This is a secure area of the application. Please confirm your password before continuing.
                    </div>
                    <form method="POST" action="{{ route('password.confirm') }}">
                        @csrf
                        <x-input.password name="password" required icon="password" center="true"/>
                        <div class="form-group row mb-0">
                            <div class="col-md-8 offset-md-4">
                                <button type="submit" class="btn btn-primary">Confirm</button>
                            </div>
                        </div>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</x-page-layout>>
