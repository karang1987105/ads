<x-page-layout>
    <x-slot name="page_title">Frequently Asked Questions</x-slot>
    <div class="row justify-content-center pb-5">
    <div class="card card-small col-md-11">
    <div class="card-header border-bottom row">
    <h5 class="col mb-0 text-center">Frequently Asked Questions</h5>
    </div>
    <ul class="list-group list-group-flush mb-1">
    <li class="list-group-item">
    <p class="bold green">Last updated on January 24th, 2024.</p>
    <h4 id="general" class="bold toc-pos">General</h4>
    <p>
    <span class="bold">Question:</span> Which cryptocurrencies are currently accepted within your service?<br>
    <span class="bold">Answer:</span> Currently we use Litecoin for all transactions.
    If you wish to use another cryptocurrency please let us know.
    </p>
    <span class="bold">Question:</span> I have a question which is not listed anywhere below, what should I do?<br>
    <span class="bold">Answer:</span> In this case please <a href="{{ route('contact.create') }}">contact<a> us and we will be happy to help you!
    </p>
    <h4 class="bold">For Publishers</h4>
    <span class="bold">Question:</span> How can I sign up as a publisher?<br>
    <span class="bold">Answer:</span> To sign up as a publisher fill out the form on our <a href="{{ route('register') }}">registration<a> page.
    </p>
    <span class="bold">Question:</span> Can someone else of my household create an account as well?<br>
    <span class="bold">Answer:</span> Yes they can also create an account.
    </p>
    <span class="bold">Question:</span> What are the requirements for my website or web application to be approved?<br>
    <span class="bold">Answer:</span> The website or web application should be around for some time, have a clean layout and content. It should not be overfilled with advertisements.
    It needs to be SSL secured and have a top-level domain. We do not accept brand new websites or web applications with no traffic.
    </p>
    <span class="bold">Question:</span> Are websites or web applications with any kind of content allowed?<br>
    <span class="bold">Answer:</span> No. We do not accept websites or web applications with any kind of violence, virusses, malware, drugs, alcohol,
    child pornography or any other illegal and aggressive content. Further it must be crypto oriented.
    </p>
    <span class="bold">Question:</span> Are websites or web applications with incentive traffic allowed?<br>
    <span class="bold">Answer:</span> Yes but it should also have other types of traffic. We will not credit your account for traffic from PTC (Paid To Click), PTP (Paid To Promote)
    or any other "Paid To" or similar traffic source. We blacklist and ignore traffic from such sources. Nevertheless it doesn't mean that you are not
    allowed to arbitrage and redirect CPC (Cost Per Click), CPM (Cost Per Mille) or CPV (Cost Per View) traffic from other advertising networks.
    Organic traffic from any source is very welcome!
    </p>
    <span class="bold">Question:</span> What about adult content?<br>
    <span class="bold">Answer:</span> Adult content is not accepted on our network.
    </p>
    <span class="bold">Question:</span> Are websites or web applications accepted automatically?<br>
    <span class="bold">Answer:</span> No. Every website or web application is being approved manually by our staff. If you edit or change it after it has been approved, it gets unapproved
    and needs to be reviewed by our staff again.
    </p>
    <span class="bold">Question:</span> How long does it usually take for a website or web application to be approved?<br>
    <span class="bold">Answer:</span> Websites and web applications are constantly being reviewed. Nevertheless please allow up to 48 hours for the process.
    </p>
    <span class="bold">Question:</span> What types of advertising do you offer?<br>
    <span class="bold">Answer:</span> Currently we work with banner and video advertising formats of different sizes.
    </p>
    <span class="bold">Question:</span> What banner and video sizes are currently supported?<br>
    <span class="bold">Answer:</span> Leaderboard 728x90, Medium Rectangle 300x250, Wide Skyscraper 160x600. Video Landscape 640x360.
    </p>
    <span class="bold">Question:</span> What advertising measurements are currently supported?<br>
    <span class="bold">Answer:</span> We work with CPM (Cost Per Mille), CPV (Cost Per View) and CPC (Cost Per Click) measurements.
    </p>
    <span class="bold">Question:</span> Do you offer fixed rates?<br>
    <span class="bold">Answer:</span> Yes but they will vary by the quality of your traffic, website category and geolocation.
    </p>
    <span class="bold">Question:</span> My account has been suspended what can I do?<br>
    <span class="bold">Answer:</span> In this case you have violated our <a href="{{ route('terms-of-service') }}">Terms Of Service<a>.
    For more information please <a href="{{ route('login') }}">login<a> to your account and open a ticket.
    </p>
    <span class="bold">Question:</span> I cannot login anymore what happened?<br>
    <span class="bold">Answer:</span> Try to <a href="{{ route('password.email') }}">recover</a> your password. In case it says "We can't find a user with that email address."
    your account has been permanently deleted due to inactivity.
    </p>
    <span class="bold">Question:</span> Is it possible to register again if my account has been deleted because of inactivity?<br>
    <span class="bold">Answer:</span> Yes you may register again if you wish.
    </p>
    <span class="bold">Question:</span> How long does it take for a withdrawal request to be processed?<br>
    <span class="bold">Answer:</span> Withdrawal requests are checked and processed constantly. Nevertheless please allow up to 48 hours for the withdrawal to arrive.
    </p>
    <span class="bold">Question:</span> What is the minimum withdrawal amount?<br>
    <span class="bold">Answer:</span> Our current minimum withdrawal amount is ${{ number_format(config('ads.minimum_withdrawal_amount'), 2) }}.
    </p>
    <h4 class="bold">For Advertisers</h4>
    <span class="bold">Question:</span> How can I sign up as an advertiser?<br>
    <span class="bold">Answer:</span> To sign up as an advertiser fill out the form on our <a href="{{ route('register') }}">registration<a> page.
    </p>
    <span class="bold">Question:</span> Can someone else of my household create an account as well?<br>
    <span class="bold">Answer:</span> Yes they can also create an account.
    </p>
    <span class="bold">Question:</span> What are the requirements for my advertisement to be approved?<br>
    <span class="bold">Answer:</span> We do not accept profit promising scams such as HYIPs, "Get rich in two minutes!" or any other pyramid schemes.
    The advertisement should have a clean layout and content. It needs to be SSL secured and have a top-level domain.
    </p>
    <span class="bold">Question:</span> Are websites or web applications with any kind of content allowed?<br>
    <span class="bold">Answer:</span> No. We do not accept websites or web applications with any kind of violence, virusses, malware, drugs, alcohol,
    child pornography or any other illegal and aggressive content.
    </p>
    <span class="bold">Question:</span> What about adult content?<br>
    <span class="bold">Answer:</span> Adult content is not accepted on our network.
    </p>
    <span class="bold">Question:</span> Are advertisements accepted automatically?<br>
    <span class="bold">Answer:</span> No. Every new advertisement will be manually reviewed by our staff. If you edit or change it after it has been approved, it gets unapproved
    and needs to be reviewed by our staff again.
    </p>
    <span class="bold">Question:</span> How long does it usually take for an advetisement to be approved?<br>
    <span class="bold">Answer:</span> Advertisements are constantly being reviewed. Nevertheless please allow up to 48 hours for the process.
    </p>
    <span class="bold">Question:</span> What types of advertising do you offer?<br>
    <span class="bold">Answer:</span> Currently we work with banner and video advertising formats of different sizes.
    </p>
    <span class="bold">Question:</span> What banner and video sizes are currently supported?<br>
    <span class="bold">Answer:</span> Leaderboard 728x90, Medium Rectangle 300x250, Wide Skyscraper 160x600. Video Landscape 640x360.
    </p>
    <span class="bold">Question:</span> What advertising measurements are currently supported?<br>
    <span class="bold">Answer:</span> We work with CPM (Cost Per Mille), CPV (Cost Per View) and CPC (Cost Per Click) measurements.
    </p>
    <span class="bold">Question:</span> My account has been suspended what should I do?<br>
    <span class="bold">Answer:</span> In this case you have violated our <a href="{{ route('terms-of-service') }}">Terms Of Service<a>.
    For more information please <a href="{{ route('login') }}">login<a> to your account and open a ticket.
    </p>
    <span class="bold">Question:</span> I cannot login anymore what happened?<br>
    <span class="bold">Answer:</span> Try to <a href="{{ route('password.email') }}">recover</a> your password. In case it says "We can't find a user with that email address."
    your account has been permanently deleted due to inactivity.
    </p>
    <span class="bold">Question:</span> Is it possible to register again if my account has been deleted because of inactivity?<br>
    <span class="bold">Answer:</span> Yes you may register again if you wish.
    </p>
    <span class="bold">Question:</span> How long does it take for a deposit to be credited?<br>
    <span class="bold">Answer:</span> Deposits are automatically credited after {{ config('ads.min_tx_confirmations') }} confirmations on the blockchain.
    If your balance doesn't update by that time and your deposit gets marked as expired, please let us know your TXID (Transaction ID)
    and invoice ID via support ticket.
    </p>
    <span class="bold">Question:</span> What is the minimum deposit amount?<br>
    <span class="bold">Answer:</span> Our current minimum deposit amount is ${{ number_format(config('ads.minimum_deposit'), 2) }}.
    </p>
    </li>
    </ul>
    </div>
    </div>
    <button onclick="location.href='#general'" class="btn-top">&#10146;</button>
</x-page-layout>