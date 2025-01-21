<x-page-layout>
    <x-slot name="page_title">Publishers</x-slot>
    <div class="row justify-content-center">
    <div class="card card-small col-md-11">
    <div class="card-header border-bottom row">
    <h5 class="col mb-0" style="text-align:center">Publishers</h5>
    </div>
    <ul class="list-group list-group-flush mb-1">
    <li class="list-group-item">
    <h4><b>Information For Publishers</b></h4>
    <p>
    Join our network as a publisher today and build a stable income 
    by providing worldwide traffic. There is no limit of your earning, the more quality traffic you send us
    the more you will earn. Why you should join our network? Check out all your benefits below.
    </p>
    <h4><b>1. Worldwide Traffic Accepted</b></h4>
    <p>
    It doesn't matter where you are based, we accept websites and web applications from all over the world.
    </p>
    <h4><b>2. No Minimum Traffic Required</b></h4>
    <p>
    We accept websites and web applications with any amount of traffic, it doesn't matter how many impressions
    and clicks you get daily, if you have organic traffic we want it.
    </p>
    <h4><b>3. High Payout Rates</b></h4>
    <p>
    We do our best to provide you with the best possible payout rates.
    </p>
    <h4><b>4. Low Withdrawal Minimum</b></h4>
    <p>
    We have a very low withdrawal minimum, currently it's only ${{ number_format(config('ads.minimum_withdrawal_amount'), 2) }}. It will not take too long
    to collect your first payout.
    </p>
    <h4><b>5. Fast Withdrawals</b></h4>
    <p>
    We always pay our publishers on time. If your account balance has a minimum of ${{ number_format(config('ads.minimum_withdrawal_amount'), 2) }}
    you can request a withdrawal which will be paid as soon as possible. Mostly during the next few hours as withdrawals are made constantly.
    Nevetheless in rarely cases it could take up to 48 hours.
    </p>
    <h4><b>6. Real Time Statistics</b></h4>
    <p>
    Once your website or web application is approved by our staff and your ad code is placed,
    you are ready to monitor all your statistics such as impressions, views, clicks and all the earnings for each country in real time.  
    </p>
    <h4><b>7. Clean Advertisements</b></h4>
    <p>
    We use several scans and other security opportunities to keep all  advertisements in our network as secure and clean as possible
    to keep your users away from malware, viruses and any other fraudent acts. Further every single advertisement is being manually reviewed
    by our staff before advertisers are able to run any campaigns.
    </p>
    <h4><b>8. Different Ad Formats</b></h4>
    <p>
    In your account you can choose from various available advertising formats to publish them on 
    your website or web application. Such as Leaderboard 728x90, Medium Rectangle 300x250, Wide Skyscraper 160x600
    or the Landscale 640x320 video format. Depending on the advertising format it's possible to choose between CPC (Cost Per Click),
    CPM (Cost Per Mille) or CPV (Cost Per View) measurements. Different formats may give you different payout rates.    
    </p>
    <h4><b>9. Secure Experience</b></h4>
    <p>
    Apart from an SSL certificate that protects all your personal 
    data, we use a bunch of other security methods to make your experience as secure as possible.
    </p>
    <h4><b>10. Friendly Support</b></h4>
    <p>
    If you have any questions or need some help please <a href="{{ route('contact.create') }}">contact</a> our support team at 
    any time. It would be a pleasure for us to assist you!
    </p>
    </li>
    </ul>
    </div>
    </div>
    <div class="pt-5"></div>
 </x-page-layout>