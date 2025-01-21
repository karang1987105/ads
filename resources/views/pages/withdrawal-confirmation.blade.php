<x-page-layout>
    <x-slot name="page_title">Withdrawal Confirmation</x-slot>
    <div class="row justify-content-center">
        <div class="card card-small col-md-6">
            <div class="card-header border-bottom row">
                <h5 class="col mb-0">Withdrawal Confirmation</h5>
                <a class="col pb-0 mb-0 mt-1 text-right" href="{{ route('dashboard') }}">Dashboard</a>
            </div>
            <ul class="list-group list-group-flush mb-1">
                <li class="list-group-item">
                    <div class="mb-4 font-medium text-sm text-green-600">
                        @isset($error)
                            {{ $error }}
                        @else
                            <p>
                                <h2 style="color:green; font-weight:bold">Withdrawal Confirmed!</h2><br>
                                <a style="font-size:18px">Requested Amount: <a style="color:green; font-weight:bold; font-size:18px">${{ $amount }}</a></a><br>
                                <a style="font-size:18px">Currency: <a style="color:green; font-weight:bold; font-size:18px">{{ $currency }}</a></a><br>
                                <a style="font-size:18px">Wallet Address: <a style="color:green; font-weight:bold; font-size:18px">{{ $wallet }}</a></a><br><br>
								<h6>NOTE: Your withdrawal request has been successfully confirmed.
								As all of the publisher payments are made manually please allow up to 24 hours for our staff to proceed.
								You can follow further status of the payment in your dashboard.</h6>
                            </p>
                        @endisset
                    </div>
                </li>
            </ul>
        </div>
    </div>
</x-page-layout>
