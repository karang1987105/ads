<!doctype html>
<html class="no-js h-100" lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }}@isset($page_title) | {{ $page_title }}@endisset</title>
	<link rel="icon" href="{{ url('images/favicon.png') }}">
    <link href="{{ asset('css/style.css') }}" rel="stylesheet">
    <script type="text/javascript">
    window.CONFIG = {
    BASE_URL: "{{ config('ads.base_url', '') }}",
    DECIMALS: {{ config('ads.crypto_decimal_places_length') }}
    }
    </script>
    <script src="{{ asset('js/app.js') }}" defer></script>
    <script src="{{ asset('js/scripts.js') }}" defer></script>
    <script src="{{ asset('js/landing.js') }}" defer></script>
</head>
<body>
    <!-- Landing Content -->
    {{ $slot }}
    <!-- Landing Content -->
@if(!isset($cookiePage) && !isset($_COOKIE['gdpr']))
    <div class="GDPR-sticky">
        <div class="promo-popup card card-small col-offset-2 col-md-8 landing-form" style="margin-top: 20px">
            <div class="card-header border-bottom row" style="background-color: #ffffff12">
                <h3 class="col mb-0 text-center">Cookie Preferences</h5>
            </div>
            <ul class="list-group list-group-flush mb-1" style="padding-left: 20px; padding-right: 20px">
                <h4 style="color: #a2c6ff !important" class="bold">We Use Cookies</h4>
                <p>
                    This Service stores data such as Cookies to enable essential functionality, as well as marketing, personalization and analytics.
                    You may change Your settings at any time or accept the default settings.
                </p>
                    For more detailed information please read our <a href="{{ route('privacy-policy') }}">Privacy Policy</a>.
                </p>
                <div class="d-flex align-items-center justify-content-center">
                    <button onclick="GDPR.agree()" class="btn mr-2" style="background-color: green; color: white;">Agree</button>
                    <button onclick="GDPR.decline()" class="btn btn-danger mr-2">Decline</button>
                    <button onclick="location.href='{{ route('cookies-preferences') }}'" class="btn btn-warning">Change Preferences</button>
                </div>
            </ul>
        </div>
    </div>
@endif
<!-- Footer -->
<div class="footer" id="footer">
  <!-- PLACE FOOTER IMAGE HERE LATER!  <img src="./images/landing/footer.png" width="105%"> -->
    <div style="margin-top: -25px">
      <img src="{{ asset('images/logo.svg') }}"  width="200px">
      <p style="color:white">Copyright © {{ date('Y') }} {{ env('APP_NAME') }}. All rights reserved.{{'./images/logo.svg'}}</p>
    </div>
    <div class="footer-links" style="margin-bottom: -25px">
      @guest
      <a href="/">Home</a>
      @else
      <a href="{{ route('dashboard') }}">Dashboard</a>
      @endguest
      <a href="{{ route('terms-of-service') }}">Terms of Service</a>
      <a href="{{ route('privacy-policy') }}">Privacy Policy</a>
      <a href="{{ route('refund-policy') }}">Refund Policy</a>
      <a href="{{ route('cookies-preferences') }}">Cookies Preferences</a>
      @guest
      @else
      <a href="{{ route('logout') }}">Logout</a>
      @endguest
    </div>
<!-- Footer -->
</body>
</html>
