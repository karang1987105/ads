<x-page-layout>
    <x-slot name="page_title">Cookies Preferences</x-slot>
    <x-slot name="cookiePage">1</x-slot>
    <style>
        .disabled {
            color: #adadad !important;
        }

        .disabled h4 {
            color: #adadad !important;
        }

        .landing-form h4 {
            color: #a2c6ff !important;
        }

        .landing-form ul.list-group {
            list-style-type: none;
        }
    </style>
    <div class="row justify-content-center pt-5 pb-4">
    <div class="card card-small col-md-6 landing-form">
    <div class="card-header border-bottom row" style="background-color: #ffffff12;">
    <h3 class="col mb-0" style="text-align:center">Cookies Preferences</h3>
    </div>
        <ul class="list-group list-group-flush mb-1">
        <li class="list-group-item">
        <form onsubmit="return false">
        <div class="border-bottom mb-4">
            <h4><b>Your Privacy Is Important To Us</b></h4>
            <p>
            Cookies are very small text files that are stored on your computer when you visit
            a website or other online service. We use cookies for a variety of purposes and to
            enhance your online experience on our service. For example to remember your account
            login details.
            </p>
            You can change your preferences anytime and decline certain types of cookies to be
            stored on your computer while browsing our service. You can also remove any cookies
            already stored on your computer, but keep in mind that deleting cookies may
            prevent you from using parts of our service.
            </p>
        </div>
        <div id="necessary-cookies" class="form-group row">
        <div class="col-md-1 col-form-label pt-3">
        <x-input.check name="cookies_first" label=" " :checked="true" disabled/>
        </div>
        <div class="col-md-11 col-form-label border-bottom">
            <h4><b>Strictly Necessary Cookies</b></h4>
            <p>
            These cookies are always active and cannot be disabled. They are essential to provide you with our
            service and to enable you to use certain features of our service.
            </p>
            Without these cookies our service would simply break and we would not be able to
            properly provide it.
            </p>
        </div>
        </div>
        <div id="functionality-cookies" class="form-group row">
        <div class="col-md-1 col-form-label pt-3">
        <x-input.check name="cookies_second" label=" " :checked="true" />
        </div>
        <div class="col-md-11 col-form-label border-bottom">
            <h4><b>Functionality Cookies</b></h4>
            <p>
            These cookies are used to provide you with enhanced and more personalized
            experience on our service and to remember choices you make when you use it.
            </p>
            For example, we may use functionality cookies to remember your preferences,
            login details or the region you are in.
            </p>
        </div>
        </div>
        <div id="tracking-cookies-1" class="form-group row">
        <div class="col-md-1 col-form-label pt-3">
        <x-input.check name="cookies_third" label=" " :checked="true" />
        </div>
        <div class="col-md-11 col-form-label border-bottom">
            <h4><b>Tracking Cookies</b></h4>
            <p>
            These cookies are used to collect information to analyze the traffic to our
            service, how visitors are using it and whether there may be technical issues.
            </p>
            For example these cookies may track things such as how long you spend on the
            service or the pages you visit which helps us to understand how we can improve
            it for you.
            </p>
            Any information collected through these tracking and performance cookies do
            not identify any individual visitor.
            </p>
        </div>
        </div>
        <div id="targeting-cookies" class="form-group row border-bottom">
        <div class="col-md-1 col-form-label pt-3">
        <x-input.check name="cookies_fourth" label=" " :checked="true" />
        </div>
        <div class="col-md-11 col-form-label">
            <h4><b>Targeting And Advertising Cookies</b></h4>
            <p>
            These cookies are used to deliver advertising that is likely to be of interest to
            you based on your browsing habits. They may also be used to limit the number of
            times you see an advertisement and measure the effectiveness of
            advertising campaigns.
            </p>
            These cookies as served by our content or advertising providers, may combine
            information they collected from our service with other information they have
            independently collected relating to your web browser's activities across their
            network of websites.
            </p>
            If you choose to remove or disable these targeting or advertising cookies, you
            will still see advertisements but they may not be relevant to you.
            </p>
        </div>
        </div>
            <h4><b>More Information</b></h4>
            <p>
            For any queries and more detailed information in relation to our Cookie Policy and your
            choices please read our <a href="{{ route('privacy-policy') }}">Privacy Policy</a>.
            If you have any further questions please contact us</a>
            directly through our contact form on top of the <a href="{{ env('APP_URL') }}">Landing Page</a>.
            </p>
        </form>
            </li>
            </ul>
        </div>
    </div>
    <div class="pt-4"></div>
    <script>
        window.addEventListener('load', function() {

            $('#functionality-cookies input').on('change', function() {
                GDPR.toggle('gdpr-functionality-cookies', this.checked);
            });

            $('#targeting-cookies input').on('change', function() {
                GDPR.toggle('gdpr-targeting-cookies', this.checked);
            });

            $('#tracking-cookies-1 input').on('change', function() {
                GDPR.toggle('gdpr-tracking-cookies', this.checked);
            });

            if (!GDPR.get('gdpr')) {
                $('#necessary-cookies input').prop('checked', true).trigger('change');
            } else {
                $('#functionality-cookies input').prop('checked', GDPR.get('gdpr-functionality-cookies')).trigger('change');
                $('#targeting-cookies input').prop('checked', GDPR.get('gdpr-targeting-cookies')).trigger('change');
                $('#tracking-cookies-1 input').prop('checked', GDPR.get('gdpr-tracking-cookies')).trigger('change');
            }
        });
    </script>
</x-page-layout>