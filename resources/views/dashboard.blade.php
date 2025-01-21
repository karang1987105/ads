<x-app-layout>
    @if(!\App\Models\User::isActive())
    <div class="mb-4 text-center"><h3 class="red bold">Your account has been suspended!</h3>
    <h4>You may <a style="text-decoration:none" href="{{ route('tickets.threads.index') }}">open a ticket</a> for more info.</h4>
    </div>
    @endif
    <div class="list">
        <div class="col">
            <div class="card card-small mb-4">
                <div class="list-header card-header border-bottom">
                    <div class="list-actions clearfix">
                        <div class="list-actions clearfix">
                            <h6 class="m-0">World Map</h6>
                            <span id="zoomOutButton" class="list-action" data-toggle="tooltip" data-orginal-title="Zoom Out">
                                <i class="material-icons">remove</i>
                            </span>
                            <span id="zoomInButton" class="list-action" data-toggle="tooltip" data-orginal-title="Zoom In">
                                <i class="material-icons">add</i>
                            </span>
                            <span id="homeButton" class="list-action" data-toggle="tooltip" data-orginal-title="Reset">
                                <i class="material-icons">home</i>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="list-body">
                    <div class="card-body p-0 pb-4">
                        <div id="chartdiv" style="width: 100%; height: 500px">
                        </div>
                        <nav></nav>
                        <div class="overlay"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Small Stats Cards -->
    @if(!empty($statCards))
    <div class="row" style="background-image: radial-gradient(#2165e5 35%, #171357, #171357);">
    <div class="col-md-12 row">
    
    <!-- <div class="col-md-12 mb-4">
        <div class="card card-small h-100">
            <div id="chartdiv" style="width: 100%; height: 500px">
            </div>
        </div>
    </div> -->
    @advertiser
    <x-dashboard-small-card title="Total Balance" value="{{ \App\Helpers\Helper::amount($advertiser['balance']) }}"/>
    @endAdvertiser
    @publisher
    <x-dashboard-small-card title="Total Balance" value="{{ \App\Helpers\Helper::amount($publisher['balance']) }}"/>
    @endPublisher
    {!! $statCards !!}
    </div>
    </div>
    @endif

    @advertiser
    {!! $advertiser['active_ads'] !!}
    @endAdvertiser
    @publisher
    {!! $publisher['active_places'] !!}
    @endPublisher
    @userType('manager("advertisement")')
    {!! $manager['active_ads'] !!}
    @endUserType
    @admin
    {!! $admin['active_ads'] !!}
    {!! $admin['active_places'] !!}
    @endAdmin
    <!-- End Small Stats Blocks -->
</x-app-layout>
<script>
    var totalData = @json($totalDataEachCountries);
    var activePlacesCountEachCountry = @json($activePlacesCountEachCountry);
    var activePlacesCountStatus = @json($activePlacesCountStatus);
    var activeCampaignsCountEachCountry = @json($activeCampaignsCountEachCountry);
    var activeCampaignsCountStatus = @json($activeCampaignsCountStatus);
    var totalPublishersCountEachCountry = @json($totalPublishersCountEachCountry);
    var totalPublishersCountStatus = @json($totalPublishersCountStatus);
    var totalPublishersBalanceEachCountry = @json($totalPublishersBalanceEachCountry);
    var totalPublishersBalanceStatus = @json($totalPublishersBalanceStatus);
    var totalAdvertisersCountEachCountry = @json($totalAdvertisersCountEachCountry);
    var totalAdvertisersCountStatus = @json($totalAdvertisersCountStatus);
    var totalAdvertisersBalanceEachCountry = @json($totalAdvertisersBalanceEachCountry);
    var totalAdvertisersBalanceStatus = @json($totalAdvertisersBalanceStatus);
</script>

<script src="{{ asset('js/world-map/index.js') }}"></script>
<script src="{{ asset('js/world-map/map.js') }}"></script>
<script src="{{ asset('js/world-map/world.js') }}"></script>
<script src="{{ asset('js/world-map/animated.js') }}"></script>
<script src="{{ asset('js/dashboard.js') }}"></script>