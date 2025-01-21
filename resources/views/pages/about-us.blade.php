<x-page-layout>
    <x-slot name="page_title">About Us</x-slot>
    <div class="row justify-content-center pb-5">
    <div class="card card-small col-md-11">
    <div class="card-header border-bottom row">
    <h5 class="col mb-0 text-center">About Us</h5>
    </div>
    <ul class="list-group list-group-flush mb-1">
    <li class="list-group-item">
    <h4 class="bold">Introduction</h4>
    <p>
    {{ env('APP_NAME') }} is an international online advertising network mainly focused on the cryptocurrency industry.
    We help our advertisers to reach their clients worldwide while we provide our publishers the opportunity to build a stable
    income for their traffic. We work with different banner and video ad formats and also different types of traffic.
    Because of a wide range of offered advertisement formats, it is possible to run advertisement campaigns of various sizes,
    types and measurements. It is also possible to run multiple campaigns for the same ad which rapidly increases visibility
    to your new potential customers. Currently we work with different banner and video ad formats, which are provided in the
    following measurements: CPM (Cost Per Mille), CPC (Cost Per Click) and CPV (Cost Per View). We carefully choose our
    publishers to provide the best possible results to our advertisers. Additionally all campaigns on <a>{{ env('APP_NAME') }}</a>
    come with optimized features and benefit from a vast array of deep targeting options. Such as country targeting while
    each country has its own cost. It is also possible to decline VPN and Proxy traffic (please note that this is still real
    human traffic, the only thing is that we cannot guarantee their actual geo location, country). We do our best to filter and block
    as much fake bot traffic as possible to provide real organic traffic only.
    </p>
    <h4 class="bold">Conclusion</h4>
    Our customers are very important to us and we work hard to ensure the best targeted audience for
    advertisers and the best rates for our publishers.
    </li>
    </ul>
    </div>
    </div>
 </x-page-layout>