<!doctype html>
<html class="no-js h-100" lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="session-lifetime" content="{{ config('session.lifetime') }}">

    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }}@isset($page_title)
        | {{ $page_title }}
        @endisset</title>
	<link rel="icon" href="{{ url('images/favicon.png') }}">
    <link href="{{ asset('css/style.css') }}" rel="stylesheet">
    <script type="text/javascript">
        window.CONFIG = {
            BASE_URL: "{{ config('ads.base_url', '') }}",
            DECIMALS: {{ config('ads.crypto_decimal_places_length') }}
        }
    </script>
    <script src="{{ asset('js/app.js') }}"></script>
    <script src="{{ asset('js/scripts.js') }}"></script>
</head>
<body class="h-100 page-body">
    <div class="container-fluid">
        <div class="row">
            <!-- Main Sidebar -->
            <aside class="main-sidebar col-12 col-md-3 col-lg-2 px-0">
                <div class="main-navbar pt-4">
                    <nav class="navbar align-items-stretch navbar-light flex-md-nowrap p-0 page-title-navbar page-title-transparent">
                        <a class="navbar-brand w-100 mr-0" href="{{ env('APP_URL') }}" style="line-height: 25px;">
                            <div class="d-table m-auto">
                                <img id="main-logo" class="d-inline-block align-top mr-1"
                                src="{{ asset('images/logo.svg') }}">
                                <span class="d-none ml-1">{{ env('APP_NAME') }}</span>
                            </div>
                        </a>
                        <a class="toggle-sidebar d-sm-inline d-md-none d-lg-none">
                            <i class="material-icons">&#xE5C4;</i>
                        </a>
                    </nav>
                </div>
                <div class="nav-wrapper pt-4">
                    <ul class="nav flex-column">
                        <li class="nav-item {{ Request::is('', 'dashboard') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ route('dashboard') }}">
                                <i class="material-icons">home</i>
                                <span>Dashboard</span>
                            </a>
                        </li>

                        @if(\App\Models\User::isActive())

                            @if(auth()->user()->isAdmin())
                                <li class="nav-item">
                                    <a class="nav-link dropdown-toggle {{ Request::is('admin/ads', 'admin/ad-types') ? 'opened' : '' }}"
                                    href="#">
                                        <i class="material-icons">ads_click</i>
                                        <span>AD Management</span>
                                    </a>
                                    <ul class="nav flex-column" {{ Request::is('admin/ads', 'admin/ad-types') ? 'style=display:block' : '' }}>
                                        <li class="nav-item {{ Request::is('admin/ads') ? 'active' : '' }}">
                                            <a class="nav-link " href="{{ route('admin.ads.advertisers-index') }}">
                                                <i class="material-icons">campaign</i>
                                                <span>Advertisements</span>
                                            </a>
                                        </li>
                                        <li class="nav-item {{ Request::is('admin/ad-types') ? 'active' : '' }}">
                                            <a class="nav-link" href="{{ route('admin.ad-types.index') }}">
                                                <i class="material-icons">filter</i>
                                                <span>AD Types</span>
                                            </a>
                                        </li>
                                        <li class="nav-item {{ Request::is('admin/blacklisting') ? 'active' : '' }}">
                                            <a class="nav-link" href="{{ route('admin.blacklisting.index') }}">
                                                <i class="material-icons">cancel_presentation</i>
                                                <span>Blacklisting</span>
                                            </a>
                                        </li>
                                    </ul>
                                </li>
                            @elseif(\App\Models\User::hasAnyPermissions('advertisements', ['Create', 'Update', 'Delete', 'Block', 'Activate']))
                                <li class="nav-item {{ Request::is('admin/ads') ? 'active' : '' }}">
                                    <a class="nav-link " href="{{ route('admin.ads.advertisers-index') }}">
                                        <i class="material-icons">campaign</i>
                                        <span>Advertisements</span>
                                    </a>
                                </li>
                            @endif

                            @admin
                            <li class="nav-item">
                                <a class="nav-link dropdown-toggle {{ Request::is('admin/geo-profiles', 'admin/categories') ? 'opened' : '' }}"
                                href="#">
                                    <i class="material-icons">public</i>
                                    <span>GEO Targeting</span>
                                </a>
                                <ul class="nav flex-column" {{ Request::is('admin/geo-profiles', 'admin/categories') ? 'style=display:block' : '' }}>
                                    <li class="nav-item {{ Request::is('admin/geo-profiles') ? 'active' : '' }}">
                                        <a class="nav-link" href="{{ route('admin.countries.index') }}">
                                            <i class="material-icons">my_location</i>
                                            <span>GEO Profiles</span>
                                        </a>
                                    </li>
                                    <li class="nav-item {{ Request::is('admin/categories') ? 'active' : '' }}">
                                        <a class="nav-link" href="{{ route('admin.categories.index') }}">
                                            <i class="material-icons">dvr</i>
                                            <span>Categories</span>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                            @endAdmin

                            @if(\App\Models\User::hasPermission('advertisers', 'List') || \App\Models\User::hasPermission('publishers', 'List'))
                                <li class="nav-item">
                                    <a href="#"
                                    class="nav-link dropdown-toggle {{ Request::is('admin/managers','admin/advertisers','admin/publishers') ? 'opened' : '' }}">
                                        <i class="material-icons">switch_account</i>
                                        <span>Users</span>
                                    </a>
                                    <ul class="nav flex-column" {{ Request::is('admin/managers','admin/advertisers','admin/publishers') ? 'style=display:block' : '' }}>
                                        @admin
                                        <li class="nav-item {{ Request::is('admin/managers') ? 'active' : '' }}">
                                            <a class="nav-link" href="{{ route('admin.managers.index') }}">
                                                <i class="material-icons">manage_accounts</i>
                                                <span>Managers</span>
                                            </a>
                                        </li>
                                        @endAdmin
                                        @if(\App\Models\User::hasPermission('advertisers', 'List'))
                                            <li class="nav-item {{ Request::is('admin/advertisers') ? 'active' : '' }}">
                                                <a class="nav-link" href="{{ route('admin.advertisers.index') }}">
                                                    <i class="material-icons">shop</i>
                                                    <span>Advertisers</span>
                                                </a>
                                            </li>
                                        @endif
                                        @if(\App\Models\User::hasPermission('publishers', 'List'))
                                            <li class="nav-item {{ Request::is('admin/publishers') ? 'active' : '' }}">
                                                <a class="nav-link" href="{{ route('admin.publishers.index') }}">
                                                    <i class="material-icons">storefront</i>
                                                    <span>Publishers</span>
                                                </a>
                                            </li>
                                        @endif
                                    </ul>
                                </li>
                            @endif

                            @php($domainManager = (\App\Helpers\Helper::isManager('publishers:Domains') || \App\Helpers\Helper::isManager('advertisers:Domains')))
                            @if(\App\Helpers\Helper::isAdmin() || (\App\Helpers\Helper::isManager('publishers:Places') && $domainManager))
                                <li class="nav-item">
                                    <a class="nav-link dropdown-toggle {{ Request::is('admin/domains', 'admin/places') ? 'opened' : '' }}"
                                    href="#">
                                        <i class="material-icons">domain</i>
                                        <span>Domains and Places</span>
                                    </a>
                                    <ul class="nav flex-column" {{ Request::is('admin/domains', 'admin/places') ? 'style=display:block' : '' }}>
                                        <li class="nav-item {{ Request::is('admin/domains') ? 'active' : '' }}">
                                            <a class="nav-link" href="{{ route('admin.domains.indexUsers') }}">
                                                <i class="material-icons">domain</i>
                                                <span>Domains</span>
                                            </a>
                                        </li>
                                        <li class="nav-item {{ Request::is('admin/places') ? 'active' : '' }}">
                                            <a class="nav-link " href="{{ route('admin.places.publishers-index') }}">
                                                <i class="material-icons">developer_mode</i>
                                                <span>Places</span>
                                            </a>
                                        </li>
                                    </ul>
                                </li>
                            @elseif($domainManager)
                                <li class="nav-item {{ Request::is('admin/domains') ? 'active' : '' }}">
                                    <a class="nav-link" href="{{ route('admin.domains.indexUsers') }}">
                                        <i class="material-icons">domain</i>
                                        <span>Domains</span>
                                    </a>
                                </li>
                            @elseif(\App\Helpers\Helper::isManager('publishers:Places'))
                                <li class="nav-item {{ Request::is('admin/places') ? 'active' : '' }}">
                                    <a class="nav-link " href="{{ route('admin.places.publishers-index') }}">
                                        <i class="material-icons">developer_mode</i>
                                        <span>Places</span>
                                    </a>
                                </li>
                            @endif

                            @userType('admin', 'manager("advertisers:Add Fund,Remove Fund", "publishers:Add Fund,Remove Fund,Withdrawal Requests", "promos:Create,Update,Delete")')
                            <li class="nav-item">
                                <a class="nav-link dropdown-toggle {{ Request::is('admin/invoices', 'admin/promos', 'admin/currencies') ? 'opened' : '' }}"
                                href="#">
                                    <i class="material-icons">account_balance</i>
                                    <span>Accounting</span>
                                </a>
                                <ul class="nav flex-column" {{ Request::is('admin/invoices', 'admin/promos', 'admin/currencies') ? 'style=display:block' : '' }}>
                                    @userType('admin', 'manager("advertisers:Add Fund,Remove Fund", "publishers:Add Fund,Remove Fund,Withdrawal Requests")')
                                    <li class="nav-item {{ Request::is('admin/invoices') ? 'active' : '' }}">
                                        <a class="nav-link" href="{{ route('admin.invoices.indexUsers') }}">
                                            <i class="material-icons">receipt_long</i>
                                            <span>Invoices</span>
                                        </a>
                                    </li>
                                    @endUserType
                                    @if(\App\Models\User::hasAnyPermissions('promos', ['Create', 'Update', 'Delete']))
                                        <li class="nav-item {{ Request::is('admin/promos') ? 'active' : '' }}">
                                            <a class="nav-link " href="{{ route('admin.promos.index') }}">
                                                <i class="material-icons">redeem</i>
                                                <span>Promo Codes</span>
                                            </a>
                                        </li>
                                    @endif
                                    @admin
                                    <li class="nav-item {{ Request::is('admin/currencies') ? 'active' : '' }}">
                                        <a class="nav-link" href="{{ route('admin.currencies.index') }}">
                                            <i class="material-icons">payments</i>
                                            <span>Currencies</span>
                                        </a>
                                    </li>
                                    @endAdmin
                                </ul>
                            </li>
                            @endUserType

                            @publisher
                            <li class="nav-item {{ Request::is('publisher/payments') ? 'active' : '' }}">
                                <a class="nav-link" href="{{ route('publisher.invoices.index') }}">
                                    <i class="material-icons">payments</i>
                                    <span>Payments</span>
                                </a>
                            </li>
                            @endPublisher

                            @userType('admin', 'manager("send_email:Create,Update,Delete,Send")')
                            <li class="nav-item {{ Request::is('admin/emails-templates') ? 'active' : '' }}">
                                <a class="nav-link" href="{{ route('admin.emails-templates.index') }}">
                                    <i class="material-icons">mail</i>
                                    <span>Mass Mails</span>
                                </a>
                            </li>
                            @endUserType

                            @publisher
                            <li class="nav-item {{ Request::is('publisher/places') ? 'active' : '' }}">
                                <a class="nav-link " href="{{ route('publisher.places.index') }}">
                                    <i class="material-icons">developer_mode</i>
                                    <span>Places</span>
                                </a>
                            </li>
                            @endPublisher

                            @advertiser
                            <li class="nav-item {{ Request::is('advertiser/invoices') ? 'active' : '' }}">
                                <a class="nav-link " href="{{ route('advertiser.invoices.index') }}">
                                    <i class="material-icons">account_balance</i>
                                    <span>Invoices</span>
                                </a>
                            </li>
                            <li class="nav-item {{ Request::is('advertiser/ads') ? 'active' : '' }}">
                                <a class="nav-link " href="{{ route('advertiser.ads.index') }}">
                                    <i class="material-icons">ads_click</i>
                                    <span>Advertisements</span>
                                </a>
                            </li>
                            <li class="nav-item {{ Request::is('advertiser/domains') ? 'active' : '' }}">
                                <a class="nav-link " href="{{ route('advertiser.domains.index') }}">
                                    <i class="material-icons">domain</i>
                                    <span>Domains</span>
                                </a>
                            </li>
                            @endAdvertiser

                            @userType('publisher')
                            <li class="nav-item {{ Request::is('publisher/domains') ? 'active' : '' }}">
                                <a class="nav-link " href="{{ route('publisher.domains.index') }}">
                                    <i class="material-icons">domain</i>
                                    <span>Domains</span>
                                </a>
                            </li>
                            @endUserType

                        @endif

                        <li class="nav-item {{ Request::is('tickets') ? 'active' : '' }}">
                            <a class="nav-link " href="{{ route('tickets.threads.index') }}">
                                <i class="material-icons">support_agent</i>
                                <span>Tickets</span>
                            </a>
                        </li>

                        <li class="nav-item {{ Request::is('logs') ? 'active' : '' }}">
                            <a class="nav-link " href="{{ route('logs.index') }}">
                                <i class="material-icons">assignment</i>
                                <span>Logs</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </aside>
            <!-- End Main Sidebar -->
            <main class="main-content col-lg-10 col-md-9 col-sm-12 p-0 offset-lg-2 offset-md-3">
                <div class="main-navbar">
                    <!-- Main Navbar -->
                    <nav class="navbar align-items-stretch navbar-light flex-md-nowrap p-0">
                        <div class="w-100 d-none d-md-flex d-lg-flex"></div>
                        <ul class="navbar-nav flex-row" style="padding-right: 60px;">
                            <li class="nav-item dropdown mb-0">
                                <a id="user-menu" class="nav-link dropdown-toggle text-nowrap px-3 mt-0 pt-0 pb-0" data-toggle="dropdown"
                                href="#"
                                role="button" aria-haspopup="true" aria-expanded="false">
                                    {{ Auth::user()->name }}
                                </a>
                                <div class="dropdown-menu dropdown-menu-small">
                                    <a class="dropdown-item" href="{{ route('profile.index') }}">
                                        <i class="material-icons">&#xE7FD;</i> Profile</a>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item text-danger" href="{{ route('logout') }}">
                                        <i class="material-icons text-danger">&#xE879;</i> Logout </a>
                                </div>
                            </li>
                            @auth
                                <li class="nav-item dropdown notifications mb-0">
                                    <a class="nav-link nav-link-icon text-center pt-0 pb-0" href="#" role="button"
                                    id="dropdownMenuLink"
                                    data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <div class="nav-link-icon__wrapper">
                                            <i class="material-icons">&#xE7F4;</i>
                                            <span class="badge badge-pill badge-danger">
                                                {{ app(\App\Services\SiteNotificationsService::class)->getCount() }}
                                            </span>
                                        </div>
                                    </a>
                                    @if(app(\App\Services\SiteNotificationsService::class)->getCount() > 0)
                                        <div class="dropdown-menu dropdown-menu-small" aria-labelledby="dropdownMenuLink">
                                            {!! app(\App\Services\SiteNotificationsService::class)->getItems() !!}
                                        </div>
                                    @endif
                                </li>
                            @endauth
                        </ul>
                        <nav class="nav">
                            <a href="#"
                            class="nav-link nav-link-icon toggle-sidebar d-sm-inline d-md-none d-lg-none text-center border-left"
                            data-toggle="collapse" data-target=".header-navbar" aria-expanded="false"
                            aria-controls="header-navbar">
                                <i class="material-icons">&#xE5D2;</i>
                            </a>
                        </nav>
                    </nav>
                </div>
                <!-- / .main-navbar -->
                <div class="alerts">{!! $alerts ?? '' !!}</div>
                <div class="main-content-container container-fluid px-4">
                    <!-- Page Header -->
                    <div class="page-header row no-gutters">
                        <div class="col-12 col-sm-4 text-center text-sm-left mb-0">
                            @if(isset($subtitle) || isset($title))
                                @isset($subtitle)
                                    <span class="text-uppercase page-subtitle">{{  $subtitle }}</span>
                                @endisset
                                @isset($title)
                                    <h3 class="page-title">{{ $title }}</h3>
                                @endisset
                            @endif
                        </div>
                        @isset($actions)
                            <div class="col-12 col-sm-4 offset-sm-4 text-right mb-0">{!! $actions !!}</div>
                        @endisset
                    </div>
                    <!-- End Page Header -->
                    {!! $slot !!}
                </div>
<!-- Footer -->
    <div class="footer" id="footer">
<!-- Probably add a footer image later    <img src="./images/landing/footer.png" width="105%"> -->
    <div style="margin-top: -255px">
      <img src="{{ asset('images/logo.svg') }}" width="200px">
      <p style="color:white">Copyright © {{ date('Y') }} {{ env('APP_NAME') }}. All rights reserved.</p>
    </div>
    <div class="footer-links" style="margin-bottom: -175px">
      <a href="{{ route('dashboard') }}">Dashboard</a>
      <a href="{{ route('terms-of-service') }}">Terms of Service</a>
      <a href="{{ route('privacy-policy') }}">Privacy Policy</a>
      <a href="{{ route('refund-policy') }}">Refund Policy</a>
      <a href="{{ route('cookies-preferences') }}">Cookies Preferences</a>
      <a href="{{ route('logout') }}">Logout</a>
    </div>
  </div>
<!-- Footer -->
            </main>
        </div>
    </div>
    <div class="modal fade" id="alert-modal" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header p-0">
                    <h4 class="modal-title ml-4 my-2"></h4>
                    <button type="button" class="close text-danger mr-3 mt-3 p-0" data-dismiss="modal">×</button>
                </div>
                <div class="modal-body">
                    <p class="modal-message mb-0"></p>
                </div>
                <div class="modal-footer pt-0 pr-3 border-0">
                    <button type="button" class="btn btn-primary" data-dismiss="modal">OK</button>
                    {{--                <button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>--}}
                </div>
            </div>
        </div>
    </div>
</body>
</html>
