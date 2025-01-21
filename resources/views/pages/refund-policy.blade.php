<x-page-layout>
    <x-slot name="page_title">Refund Policy</x-slot>
    <style>
        .landing-form h4 {
            color: #a2c6ff !important;
        }
        
        .landing-form ul.list-group {
            list-style-type: none;
        }
    </style>
    <div class="row justify-content-center pt-5 pb-5">
        <div class="card card-small col-md-11 landing-form">
            <div class="card-header border-bottom row" style="background-color: #ffffff12;">
                <h3 class="col mb-0 text-center">Refund Policy</h3>
            </div>
            <ul class="list-group list-group-flush mb-1">
                <li class="list-group-item">
                    <p class="bold green">Last updated on January 24th, 2023.</p>
                    <h4 span class="bold">Refund Policy</h4>
                    This Refund Policy covers the circumstances under which {{ env('APP_NAME') }} may in its discretion provide refunds
                    and is part of the <a href="{{ route('terms-of-service') }}">Terms Of Service</a>. The use of the terms "we", "us" and "our"
                    in this Refund Policy means {{ env('APP_NAME') }}. Capitalized words not defined in this Refund Policy have the meaning
                    as defined in the Terms Of Service. By using our web application you are accepting this Refund Policy. If you do not agree
                    to the Refund Policy, please do not use our web application.
                    </p>
                    <h4 class="bold">Amendments And Acceptance</h4>
                    We may change our Refund Policy at any time by posting a revised Refund Policy on this page or sending information
                    regarding the changes to the email address you provide to us during registration. You are responsible for regularly reviewing
                    this page to obtain timely notice of possible changes. The easiest way to do so is to check the date on top on which the
                    Refund Policy has been updated. It is assumed that you have read and accepted the changes by continued use of this
                    web application after the changes have been posted or information about them has been sent to you.
                    </p>
                    <h4 span class="bold">Main Balance</h4>
                    The main balance is the amount you see on your dashboard, which can be used to purchase campaigns for your advertisements.
                    As soon as you create a campaign, funds are moved from your main balance into reserved funds for that campaign to cover
                    its costs. Main balance can be obtained with deposits by yourself or by manual credit from one of our staff members.
                    You are not entitled to any refunds for funds that has already been spent on your campaigns. We do not take any responsibility
                    for any losses due to the use of any third party services for deposits to your main balance. In case of a refund please allow
                    up to 48 hours for your refund to arrive, in rare cases it may take longer.
                    </p>
                    <h4 span class="bold">Refunds From Active Campaigns</h4>
                    You must be fully aware of the details about your active campaigns and be sure that you exactly understand what you
                    have purchased including but not limited to the price, advertisement type, number of whether impressions, views or clicks
                    sold and that all advertisements do constantly rotate, which causes a constant decrease of your current campaign balance,
                    unless you stop the campaign. All remaining funds of active campaigns will be refunded back to your main balance automatically
                    as soon as you stop and remove your active campaign. Please note that only funds on your main balance can actually
                    be refunded and send back to you.
                    </p>
                    <h4 span class="bold">Refund Conditions</h4>
                    You are entitled to request a refund if you accidently have deposited a much larger amount as planned or if you are no longer
                    satisfied with our service and want to get back your remaining balance. Your deposit must not be more than 14 days ago,
                    after this period your deposit will no longer be refundable. Please note that we do not refund amounts below $25.00.
                    If you wish to request a refund please login to your account and create a support ticket.
                    </p>
                </li>
            </ul>
        </div>
    </div>
</x-page-layout>