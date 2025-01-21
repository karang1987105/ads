<x-page-layout title='Home'>
  <div class="preloader">
    <div class="preloader-items">
      <svg viewBox="0 0 200 200">
      <rect fill="#15ECFF" stroke="#15ECFF" stroke-width="13" width="30" height="30" x="25" y="85">
          <animate attributeName="opacity"
          calcMode="spline" dur="2" values="1;0;1;" keySplines=".5 0 .5 1;.5 0 .5 1" repeatCount="indefinite" begin="-.4"></animate>
        </rect>
        <rect fill="#15ECFF"
          stroke="#15ECFF" stroke-width="13" width="30" height="30" x="85" y="85">
          <animate attributeName="opacity" calcMode="spline" dur="2" values="1;0;1;"
          keySplines=".5 0 .5 1;.5 0 .5 1" repeatCount="indefinite" begin="-.2"></animate>
        </rect>
        <rect fill="#15ECFF" stroke="#15ECFF" stroke-width="13"
          width="30" height="30" x="145" y="85">
          <animate attributeName="opacity" calcMode="spline" dur="2" values="1;0;1;" keySplines=".5 0 .5 1;.5 0 .5 1"
          repeatCount="indefinite" begin="0"></animate>
        </rect>
      </svg>
      <img src="{{ asset('images/logo.svg') }}">
    </div>
  </div>
  <div class="main-container" id="main-container">
    <div class="main-header">
      <div class="main-header-left-items">
        <div class="logo-container">
          <div>
            <a href="{{ env('APP_URL') }}"><img style="max-width: 200px;" src="{{ asset('images/logo.svg') }}"></a>
          </div>
        </div>
        <nav>
          <a href="#advertisers">Advertisers</a>
          <a href="#publishers">Publishers</a>
          <a href="#faq">FAQ</a>
          <a href="#about-us">About Us</a>
          <a href="javascript:void(0)" id="contact-button" onclick="toggleAccountModal(this, 'contact')" data-target="register-dialog">Contact</a>
        </nav>
      </div>
      <div class="main-account-controls">
        @guest
        <a id="login-button" href="javascript:void(0)" onclick="toggleAccountModal(this, 'login')" data-toggle="modal" data-target="register-dialog">Login</a>
        <a id="register-button" href="javascript:void(0)" onclick="toggleAccountModal(this, 'register')" data-target="register-dialog">Register</a>
        @else
        <a href="{{route('dashboard')}}">Dashboard</a>
        <a href="{{route('logout')}}">Logout</a>
        @endguest
      </div>
    </div>

    <div class="main-header-mobile">
      <a href="/"><img src="./images/logo.svg" alt="{{ env('APP_NAME') }} logo" width="120px" /></a>
      <button onclick="toggleMobileSidebar(this)" data-target="mobile-sidebar" type="button"><svg width="2rem" height="2rem" viewBox="0 0 24 24">
          <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15h14M5 9h14" />
        </svg></button>
    </div>

    <div class="mobile-sidebar" id="mobile-sidebar" data-isopen="false">
      <div class="sidebar-container">
        <div class="sidebar-top">
          <a href="/">
            <div class="mobile-logo-container">
              <img src="./images/logo.svg" alt="CryptoAD logo" width="120px" />
            </div>
          </a>
          <div class="mobile-navigation">
            <div class="mobile-navigation-item">
              <svg width="1.5rem" height="1.5rem" viewBox="0 0 24 24">
                <path fill="currentColor" d="M11.71 17.99A5.993 5.993 0 0 1 6 12c0-3.31 2.69-6 6-6c3.22 0 5.84 2.53 5.99 5.71l-2.1-.63a3.999 3.999 0 1 0-4.81 4.81zM22 12c0 .3-.01.6-.04.9l-1.97-.59c.01-.1.01-.21.01-.31c0-4.42-3.58-8-8-8s-8 3.58-8 8s3.58 8 8 8c.1 0 .21 0 .31-.01l.59 1.97c-.3.03-.6.04-.9.04c-5.52 0-10-4.48-10-10S6.48 2 12 2s10 4.48 10 10m-3.77 4.26l2.27-.76c.46-.15.45-.81-.01-.95l-7.6-2.28c-.38-.11-.74.24-.62.62l2.28 7.6c.14.47.8.48.95.01l.76-2.27l3.91 3.91c.2.2.51.2.71 0l1.27-1.27c.2-.2.2-.51 0-.71z" />
              </svg>
              <a href="#advertisers" data-target="mobile-sidebar" onclick="toggleMobileSidebar(this)">Advertisers</a>
            </div>

            <div class="mobile-navigation-item">
              <svg width="1.5rem" height="1.5rem" viewBox="0 0 24 24">
                <path fill="currentColor" d="m11 11.85l-1.875 1.875q-.3.3-.712.288T7.7 13.7q-.275-.3-.288-.7t.288-.7l3.6-3.6q.15-.15.325-.212T12 8.425t.375.063t.325.212l3.6 3.6q.3.3.288.7t-.288.7q-.3.3-.712.313t-.713-.288L13 11.85V19q0 .425-.288.713T12 20t-.712-.288T11 19zM4 8V6q0-.825.588-1.412T6 4h12q.825 0 1.413.588T20 6v2q0 .425-.288.713T19 9t-.712-.288T18 8V6H6v2q0 .425-.288.713T5 9t-.712-.288T4 8" />
              </svg>
              <a href="#publishers" data-target="mobile-sidebar" onclick="toggleMobileSidebar(this)">Publishers</a>
            </div>

            <div class="mobile-navigation-item">
              <svg width="1.5rem" height="1.5rem" viewBox="0 0 24 24">
                <path fill="currentColor" fill-rule="evenodd" d="M9.945 1.25h4.11c1.368 0 2.47 0 3.337.117c.9.12 1.658.38 2.26.981c.602.602.86 1.36.982 2.26c.116.867.116 1.97.116 3.337v8.11c0 1.367 0 2.47-.116 3.337c-.121.9-.38 1.658-.982 2.26c-.602.602-1.36.86-2.26.982c-.867.116-1.97.116-3.337.116h-4.11c-1.367 0-2.47 0-3.337-.116c-.9-.122-1.658-.38-2.26-.982c-.601-.602-.86-1.36-.981-2.26a11.487 11.487 0 0 1-.082-.943a.746.746 0 0 1-.016-.392a65.809 65.809 0 0 1-.019-2.002v-8.11c0-1.367 0-2.47.117-3.337c.12-.9.38-1.658.982-2.26c.601-.602 1.36-.86 2.26-.981c.866-.117 1.969-.117 3.336-.117m-5.168 17c.015.353.039.664.076.942c.099.734.28 1.122.556 1.399c.277.277.666.457 1.4.556c.755.101 1.756.103 3.191.103h4c1.436 0 2.437-.002 3.192-.103c.734-.099 1.122-.28 1.4-.556c.196-.196.343-.449.448-.841H8a.75.75 0 0 1 0-1.5h11.223c.019-.431.025-.925.026-1.5H7.898c-.978 0-1.32.006-1.582.077a2.25 2.25 0 0 0-1.54 1.422m14.473-3H7.782c-.818 0-1.376 0-1.855.128a3.748 3.748 0 0 0-1.177.548V8c0-1.435.002-2.437.103-3.192c.099-.734.28-1.122.556-1.399c.277-.277.666-.457 1.4-.556c.755-.101 1.756-.103 3.191-.103h4c1.436 0 2.437.002 3.192.103c.734.099 1.122.28 1.4.556c.276.277.456.665.555 1.4c.102.754.103 1.756.103 3.191zM7.25 7A.75.75 0 0 1 8 6.25h8a.75.75 0 0 1 0 1.5H8A.75.75 0 0 1 7.25 7m0 3.5A.75.75 0 0 1 8 9.75h5a.75.75 0 0 1 0 1.5H8a.75.75 0 0 1-.75-.75" clip-rule="evenodd" />
              </svg>
              <a href="#faq" data-target="mobile-sidebar" onclick="toggleMobileSidebar(this)">FAQ</a>
            </div>

            <div class="mobile-navigation-item">
              <svg width="1.5rem" height="1.5rem" viewBox="0 0 24 24">
                <path fill="currentColor" d="M11 9h2V7h-2m1 13c-4.41 0-8-3.59-8-8s3.59-8 8-8s8 3.59 8 8s-3.59 8-8 8m0-18A10 10 0 0 0 2 12a10 10 0 0 0 10 10a10 10 0 0 0 10-10A10 10 0 0 0 12 2m-1 15h2v-6h-2z" />
              </svg>
              <a href="#about-us" data-target="mobile-sidebar" onclick="toggleMobileSidebar(this)">About Us</a>
            </div>

            <div class="mobile-navigation-item">
              <svg width="1.5rem" height="1.5rem" viewBox="0 0 24 24">
                <path fill="currentColor" d="m12 22l-.25-3h-.25q-3.55 0-6.025-2.475T3 10.5t2.475-6.025T11.5 2q1.775 0 3.313.662t2.7 1.825t1.824 2.7T20 10.5q0 1.875-.612 3.6t-1.676 3.2t-2.525 2.675T12 22m2-3.65q1.775-1.5 2.888-3.512T18 10.5q0-2.725-1.888-4.612T11.5 4T6.888 5.888T5 10.5t1.888 4.613T11.5 17H14zm-2.525-2.375q.425 0 .725-.3t.3-.725t-.3-.725t-.725-.3t-.725.3t-.3.725t.3.725t.725.3M10.75 12.8h1.5q0-.75.15-1.05t.95-1.1q.45-.45.75-.975t.3-1.125q0-1.275-.862-1.912T11.5 6q-1.1 0-1.85.613T8.6 8.1l1.4.55q.125-.425.475-.837T11.5 7.4t1.013.375t.337.825q0 .425-.25.763t-.6.687q-.875.75-1.062 1.188T10.75 12.8m.75-1.625" />
              </svg>
              <!-- <a href="{{route('contact.create')}}" data-target="mobile-sidebar" onclick="toggleMobileSidebar(this)">Contact</a> -->
              <a href="javascript:void(0)" id="contact-button" onclick="toggleAccountModal(this, 'contact')" data-target="register-dialog">Contact</a>
            </div>
          </div>
        </div>
        <div class="sidebar-bottom">
          <div class="mobile-account-controls">
            @guest
            <a id="login-button" href="javascript:void(0)" onclick="toggleAccountModal(this, 'login')" data-target="register-dialog">Log-in</a>
            <a id="register-button" href="javascript:void(0)" onclick="toggleAccountModal(this, 'register')" data-target="register-dialog">Register</a>
            @else
            <a href="{{route('dashboard')}}">Dashboard</a>
            <a href="{{route('logout')}}">Logout</a>
            @endguest
          </div>
        </div>
      </div>
      <div class="sidebar-toggle-container">
        <button type="button" class="mobile-sidebar-close-button" onclick="toggleMobileSidebar(this)" data-target="mobile-sidebar">
          <svg width="2rem" height="2rem" viewBox="0 0 48 48">
            <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="m14 14l20 20m-20 0l20-20" />
          </svg>
        </button>
      </div>
    </div>

    <div class="section hero-section">
      <div class="hero-text">
        <p class="hero-text-big">
          ORGANIC
          <br>
          GLOBAL-REACH
          <br>
          TARGETED RESULTS
        </p>
        <p class="hero-text-small">
          {{env('APP_NAME')}} is a trusted and secure crypto-focused advertising network
        </p>
      </div>
      <div class="hero-image-container">
        <img src="./images/landing/cube.png">
      </div>
    </div>
    <div style="background-image: linear-gradient(#171357, #060a93, #171357);">
      <div id="about-us" class="section about-us-section">
        <h2>Know About Us</h2>
        <div class="accordion-container">
          <div class="accordion">
            <div class="accordion-intro">
              <h3>What is CryptoAD?</h3>
            </div>
            <div class="accordion-content">
              <p>CryptoAD is an international online advertising network mainly focused on the cryptocurrency industry. We help our advertisers to reach their clients worldwide while we provide our publishers the opportunity to build a stable income for their traffic. We work with different banner and video ad formats and also different types of traffic. Because of a wide range of offered advertisement formats, it is possible to run advertisement campaigns of various sizes, types and measurements.</p>
            </div>
          </div>

          <div class="accordion">
            <div class="accordion-intro">
              <h3>Ad Formats and Campaign Options</h2>
            </div>
            <div class="accordion-content">
              <p>It is also possible to run multiple campaigns for the same ad which rapidly increases visibility to your new potential customers. Currently we work with different banner and video ad formats, which are provided in the following measurements: CPM (Cost Per Mille), CPC (Cost Per Click) and CPV (Cost Per View). We carefully choose our publishers to provide the best possible results to our advertisers.</p>
            </div>
          </div>

          <div class="accordion">
            <div class="accordion-intro">
              <h3>Publisher Selection and Targeting Options</h3>
            </div>
            <div class="accordion-content">
              <p>Additionally all campaigns on CryptoAD come with optimized features and benefit from a vast array of deep targeting options. Such as country targeting while each country has its own cost. It is also possible to decline VPN and Proxy traffic (please note that this is still real human traffic, the only thing is that we cannot guarantee their actual geo location, country). We do our best to filter and block as much fake bot traffic as possible to provide real organic traffic only.</p>
            </div>
          </div>

          <div class="accordion">
            <div class="accordion-intro">
              <h3>Traffic Quality Control and Filtering</h3>
            </div>
            <div class="accordion-content">
              <p>CryptoAD strives to filter and block as much fake bot traffic as possible to ensure that only real organic traffic is provided. Additionally, the platform offers the option to decline VPN and Proxy traffic, although it is clarified that this traffic consists of real human visitors, with the only limitation being the inability to guarantee their actual geographical location. By implementing these measures, CryptoAD aims to maintain the integrity and quality of the traffic directed towards advertisers.</p>
            </div>
          </div>
        </div>
      </div>
    </div>


    <div id="advertisers" class="section feature-section" style="background-image: radial-gradient(#2165e5 35%, #171357, #171357);
          background-size: cover;
          background-repeat: no-repeat;
          background-position: 0 50%;">

      <div class="feature-section-inner-wrapper">
        <div class="feature-section-header">
          <div class="feature-image-container">
            <img src="./images/landing/cube.png" alt="cube">
          </div>
          <div class="feature-text">
            <h2 class="feature-heading">Elevate Your Advertising Strategy</h2>
            <p class="feature-sub-heading">Join our network as an advertiser and expose your project or ICO to our network of crypto oriented websites and web applications to increase your sales and get new clients. Why you should advertise with us? Please check out all your benefits below.</p>
          </div>
        </div>

        <div class="benefits">

          <div class="benefit traffic" style="grid-area: traffic;background-image: url(./images/landing/feature_ad.png);
                background-size: cover;
                background-repeat: no-repeat;
                background-position: bottom;
                background-color: #1c5cb1;">
            <div class="benefit-text">
              <img src="./images/landing/traffic.png" alt="traffic icon" />
              <div>
                <h2 class="benefit-text-header">Worldwide Traffic Available</h2>
                <p class="benefit-text-description">In our network we have publishers from all over the world ready to publish your project on their websites and web applications. Nothing stands in your way to reach new crypto oriented clients!</p>
              </div>
            </div>
          </div>

          <div class="benefit geotargeting" style="grid-area: geotargeting;background-image: url(./images/landing/about_bg.png);
                background-size: cover;
                background-repeat: no-repeat;
                background-position: bottom;
                background-color: #821cb1;">
            <div class="benefit-text">
              <img src="./images/landing/geotargeting.png" alt="geotargeting icon" />
              <div>
                <h2 class="benefit-text-header">Geotargeting And Filtering</h2>
                <p class="benefit-text-description">We cater to advertiser needs with geotargeting and filtering options. Geotargeting lets you advertise in selected countries with individual rates. Our filters can exclude Proxy and VPN traffic, ensuring real human visits, though their exact geolocation may not be guaranteed. If geolocation is crucial, apply this filter when setting up your ad campaign.</p>
              </div>
            </div>
          </div>

          <div class="benefit organic" style="grid-area: organic;background-image: url(./images/landing/about_bg.png);
                background-size: cover;
                background-repeat: no-repeat;
                background-position: bottom;
                background-color: #821cb1;">
            <div class="benefit-text">
              <img src="./images/landing/organic.png" alt="organic icon" />
              <div>
                <h2 class="benefit-text-header">Unique And Organic Traffic</h2>
                <p class="benefit-text-description">We aim to meet advertiser expectations by offering geotargeting and filtering options. Geotargeting lets you advertise in specific countries with varying rates. Our filters can exclude Proxy and VPN traffic, ensuring real human visits, though their exact geolocation may not be guaranteed. If geolocation is crucial, use this filter when setting up your ad campaign.</p>
              </div>
            </div>
          </div>

          <div class="benefit delivery" style="grid-area: delivery;background-image: url(./images/landing/feature_ad.png);
                background-size: cover;
                background-repeat: no-repeat;
                background-position: bottom;
                background-color: #1c5cb1;">
            <div class="benefit-text">
              <img src="./images/landing/fast.png" alt="fast icon" />
              <div>
                <h2 class="benefit-text-header">Fast Delivery</h2>
                <p class="benefit-text-description">Due to a massive network of crypto oriented websites and web applications from all over the world, it will not take too long for your ad campaign to be filled. Apart from that we have several priority options to deliver your ordered traffic as fast as possible.</p>
              </div>
            </div>
          </div>

          <div class="benefit formats" style="grid-area: formats;background-image: url(./images/landing/feature_ad.png);
                background-size: cover;
                background-repeat: no-repeat;
                background-position: bottom;
                background-color: #1c5cb1;">
            <div class="benefit-text">
              <img src="./images/landing/formats.png" alt="formats icon" />
              <div>
                <h2 class="benefit-text-header">Different Ad Formats</h2>
                <p class="benefit-text-description">Advertisers can choose from different banner formats such as Leaderboard 728x90, Medium Rectangle 300x250, Wide Skyscraper 160x600 or the Landscale 640x320 video format. Depending on the advertising format it's possible to choose between CPC (Cost Per Click), CPM (Cost Per Mille) or CPV (Cost Per View) measurements. Some formats or measurements may be more effective than others. You should try them out to see which one works best for your ICO or crypto project.</p>
              </div>
            </div>
          </div>

          <div class="benefit prices" style="grid-area: prices;background-image: url(./images/landing/about_bg.png);
                background-size: cover;
                background-repeat: no-repeat;
                background-position: bottom;
                background-color: #821cb1;">
            <div class="benefit-text">
              <img src="./images/landing/prices.png" alt="prices icon" />
              <div>
                <h2 class="benefit-text-header">Low Prices</h2>
                <p class="benefit-text-description">We work hard to keep a reliable balance between publishers and advertisers. We do our best to come up with the best possible rates for our publishers and provide our advertisers with the lowest possible prices.</p>
              </div>
            </div>
          </div>

          <div class="benefit statistics" style="grid-area: statistics;background-image: url(./images/landing/about_bg.png);
                background-size: cover;
                background-repeat: no-repeat;
                background-position: bottom;
                background-color: #821cb1;">
            <div class="benefit-text">
              <img src="./images/landing/statistics.png" alt="statistics icon" />
              <div>
                <h2 class="benefit-text-header">Real Time Statistics</h2>
                <p class="benefit-text-description">Once your advertisement has been approved by our staff, you can start campaigns and monitor important statistics such as impressions, views and clicks. Keep also an overview of all the costs of your running campaigns. All statistics are individual for each country and are in real time.</p>
              </div>
            </div>
          </div>

          <div class="benefit clean" style="grid-area: clean;background-image: url(./images/landing/feature_ad.png);
                background-size: cover;
                background-repeat: no-repeat;
                background-position: bottom;
                background-color: #1c5cb1;">
            <div class="benefit-text">
              <img src="./images/landing/clean.png" alt="clean icon" />
              <div>
                <h2 class="benefit-text-header">Clean Traffic</h2>
                <p class="benefit-text-description">Clean and legal advertising is all we want and that is why we will never partner with illegal or adult websites or web applications. We do our best to filter incoming traffic as good as possible to provide real and organic traffic only and reduce your advertising costs.<br>We also regularly check our partners for clean, legal and non adult content.</p>
              </div>
            </div>
          </div>

          <div class="benefit secure" style="grid-area: secure;background-image: url(./images/landing/feature_ad.png);
                background-size: cover;
                background-repeat: no-repeat;
                background-position: bottom;
                background-color: #1c5cb1;">
            <div class="benefit-text">
              <img src="./images/landing/secure.png" alt="secure icon" />
              <div>
                <h2 class="benefit-text-header">Secure Experience</h2>
                <p class="benefit-text-description">Apart from an SSL certificate that protects all your personal data, we use a bunch of other security methods to make your experience as secure as possible.</p>
              </div>
            </div>
          </div>

          <div class="benefit friendly" style="grid-area: friendly;background-image: url(./images/landing/about_bg.png);
                background-size: cover;
                background-repeat: no-repeat;
                background-position: bottom;
                background-color: #821cb1;">
            <div class="benefit-text">
              <img src="./images/landing/support.png" alt="support icon" />
              <div>
                <h2 class="benefit-text-header">Friendly Support</h2>
                <p class="benefit-text-description">If you have any questions or need some help please <a href="https://cryptoad.pro/contact">contact</a> our support team at any time. It would be a pleasure for us to assist you!</p>
              </div>
            </div>
          </div>

        </div>
      </div>

    </div>

    <div style="background-image: conic-gradient(#171357, #171357, #073043, #171357, #171357, #171357, #340779, #171357, #171357);">
      <div class="section faq-section">

        <div class="accordion-container">

          <h3 style="text-align: center;">Advertiser FAQ</h3>

          <div class="accordion">
            <div class="accordion-intro">
              <h3>How can I sign up as an advertiser?</h3>
            </div>
            <div class="accordion-content">
              <p>To sign up as an advertiser, fill out the form on our registration page.</p>
            </div>
          </div>

          <div class="accordion">
            <div class="accordion-intro">
              <h3>Can someone else of my household create an account as well?</h3>
            </div>
            <div class="accordion-content">
              <p>Yes, they can also create an account.</p>
            </div>
          </div>

          <div class="accordion">
            <div class="accordion-intro">
              <h3>What are the requirements for my advertisement to be approved?</h3>
            </div>
            <div class="accordion-content">
              <p>We do not accept profit promising scams such as HYIPs, "Get rich in two minutes!" or any other pyramid schemes. The advertisement should have a clean layout and content. It needs to be SSL secured and have a top-level domain.</p>
            </div>
          </div>

          <div class="accordion">
            <div class="accordion-intro">
              <h3>Are websites or web applications with any kind of content allowed?</h3>
            </div>
            <div class="accordion-content">
              <p>No. We do not accept websites or web applications with any kind of violence, viruses, malware, drugs, alcohol, child pornography or any other illegal and aggressive content.</p>
            </div>
          </div>

          <div class="accordion">
            <div class="accordion-intro">
              <h3>What about adult content?</h3>
            </div>
            <div class="accordion-content">
              <p>Adult content is not accepted on our network.</p>
            </div>
          </div>

          <div class="accordion">
            <div class="accordion-intro">
              <h3>Are advertisements accepted automatically?</h3>
            </div>
            <div class="accordion-content">
              <p>No. Every new advertisement will be manually reviewed by our staff. If you edit or change it after it has been approved, it gets unapproved and needs to be reviewed by our staff again.</p>
            </div>
          </div>

          <div class="accordion">
            <div class="accordion-intro">
              <h3>How long does it usually take for an advertisement to be approved?</h3>
            </div>
            <div class="accordion-content">
              <p>Advertisements are constantly being reviewed. Nevertheless, please allow up to 48 hours for the process.</p>
            </div>
          </div>

          <div class="accordion">
            <div class="accordion-intro">
              <h3>What types of advertising do you offer?</h3>
            </div>
            <div class="accordion-content">
              <p>Currently, we work with banner and video advertising formats of different sizes.</p>
            </div>
          </div>

          <div class="accordion">
            <div class="accordion-intro">
              <h3>What banner and video sizes are currently supported?</h3>
            </div>
            <div class="accordion-content">
              <p>Leaderboard 728x90, Medium Rectangle 300x250, Wide Skyscraper 160x600. Video Landscape 640x360.</p>
            </div>
          </div>

          <div class="accordion">
            <div class="accordion-intro">
              <h3>What advertising measurements are currently supported?</h3>
            </div>
            <div class="accordion-content">
              <p>We work with CPM (Cost Per Mille), CPV (Cost Per View), and CPC (Cost Per Click) measurements.</p>
            </div>
          </div>

          <div class="accordion">
            <div class="accordion-intro">
              <h3>Do you offer fixed rates?</h3>
            </div>
            <div class="accordion-content">
              <p>Yes, but they will vary by the quality of your traffic, website category, and geolocation.</p>
            </div>
          </div>

          <div class="accordion">
            <div class="accordion-intro">
              <h3>My account has been suspended what should I do?</h3>
            </div>
            <div class="accordion-content">
              <p>In this case, you have violated our Terms Of Service. For more information, please log in to your account and open a ticket.</p>
            </div>
          </div>

          <div class="accordion">
            <div class="accordion-intro">
              <h3>I cannot log in anymore what happened?</h3>
            </div>
            <div class="accordion-content">
              <p>Try to recover your password. In case it says "We can't find a user with that email address," your account has been permanently deleted due to inactivity.</p>
            </div>
          </div>

          <div class="accordion">
            <div class="accordion-intro">
              <h3>Is it possible to register again if my account has been deleted because of inactivity?</h3>
            </div>
            <div class="accordion-content">
              <p>Yes, you may register again if you wish.</p>
            </div>
          </div>

          <div class="accordion">
            <div class="accordion-intro">
              <h3>How long does it take for a withdrawal request to be processed?</h3>
            </div>
            <div class="accordion-content">
              <p>Withdrawal requests are checked and processed constantly. Nevertheless, please allow up to 48 hours for the withdrawal to arrive.</p>
            </div>
          </div>

          <div class="accordion">
            <div class="accordion-intro">
              <h3>What is the minimum withdrawal amount?</h3>
            </div>
            <div class="accordion-content">
              <p>Our current minimum withdrawal amount is $1.00.</p>
            </div>
          </div>

        </div>
      </div>
    </div>


    <div id="publishers" class="section feature-section" style="background-image: radial-gradient(#2165e5 35%, #171357, #171357);
          background-size: cover;
          background-repeat: no-repeat;
          background-position: center;">

      <div class="feature-section-inner-wrapper">

        <div class="feature-section-header">
          <div class="feature-text">
            <h2 class="feature-heading">Join Our Publisher Network</h2>
            <p class="feature-sub-heading">Join our network as a publisher today and build a stable income by providing worldwide traffic. There is no limit of your earning, the more quality traffic you send us the more you will earn. Why you should join our network? Check out all your benefits below.</p>
          </div>
          <div class="feature-image-container">
            <img src="./images/landing/cube.png" alt="cube">
          </div>
        </div>

        <div class="benefits publisher-benefits">

          <div class="benefit b-worldwide" style="grid-area: b-worldwide;background-image: url(./images/landing/feature_ad.png);
                background-size: cover;
                background-repeat: no-repeat;
                background-position: bottom;
                background-color: #1c5cb1;">
            <div class="benefit-text">
              <svg width="4rem" height="4rem" viewBox="0 0 256 256">
                <path fill="currentColor" d="M128 24a104 104 0 1 0 104 104A104.12 104.12 0 0 0 128 24m78.36 64h-35.65a135.28 135.28 0 0 0-22.3-45.6A88.29 88.29 0 0 1 206.37 88Zm9.64 40a87.61 87.61 0 0 1-3.33 24h-38.51a157.44 157.44 0 0 0 0-48h38.51a87.61 87.61 0 0 1 3.33 24m-88-85a115.27 115.27 0 0 1 26 45h-52a115.11 115.11 0 0 1 26-45m-26 125h52a115.11 115.11 0 0 1-26 45a115.27 115.27 0 0 1-26-45m-3.9-16a140.84 140.84 0 0 1 0-48h59.88a140.84 140.84 0 0 1 0 48Zm50.35 61.6a135.28 135.28 0 0 0 22.3-45.6h35.66a88.29 88.29 0 0 1-58 45.6Z" />
              </svg>
              <h2 class="benefit-text-header">Worldwide Traffic Accepted</h2>
              <div>
                <p class="benefit-text-description">It doesn't matter where you are based, we accept websites and web applications from all over the world.</p>
              </div>
            </div>
          </div>

          <div class="benefit b-minimum-traffic" style="grid-area: b-minimum-traffic;background-image: url(./images/landing/about_bg.png);
                background-size: cover;
                background-repeat: no-repeat;
                background-position: bottom;
                background-color: #821cb1;">
            <div class="benefit-text">
              <svg width="4rem" height="4rem" viewBox="0 0 32 32">
                <path fill="currentColor" d="M26 13a4.005 4.005 0 0 0 4-4V6h-3a3.979 3.979 0 0 0-2.747 1.106A6.004 6.004 0 0 0 19 4h-3v3a6.007 6.007 0 0 0 6 6h1v13H11v-5h1a4.005 4.005 0 0 0 4-4v-3h-3a3.979 3.979 0 0 0-2.747 1.106A6.004 6.004 0 0 0 5 12H2v3a6.007 6.007 0 0 0 6 6h1v5H2v2h28v-2h-5V13Zm-1-3a2.002 2.002 0 0 1 2-2h1v1a2.002 2.002 0 0 1-2 2h-1Zm-14 8a2.002 2.002 0 0 1 2-2h1v1a2.002 2.002 0 0 1-2 2h-1Zm-2 1H8a4.005 4.005 0 0 1-4-4v-1h1a4.005 4.005 0 0 1 4 4Zm14-8h-1a4.005 4.005 0 0 1-4-4V6h1a4.005 4.005 0 0 1 4 4Z" />
              </svg>
              <h2 class="benefit-text-header">No Minimum Traffic Required</h2>
              <div>
                <p class="benefit-text-description">We accept websites and web applications with any amount of traffic, it doesn't matter how many impressions and clicks you get daily, if you have organic traffic we want it.</p>
              </div>
            </div>
          </div>

          <div class="benefit b-payout" style="grid-area: b-payout;background-image: url(./images/landing/about_bg.png);
                background-size: cover;
                background-repeat: no-repeat;
                background-position: bottom;
                background-color: #821cb1;">
            <div class="benefit-text">
              <svg width="4rem" height="4rem" viewBox="0 0 24 24">
                <path fill="currentColor" d="M17 4a1 1 0 1 1 0-2h4a1 1 0 0 1 1 1v4a1 1 0 1 1-2 0V5.414l-5.793 5.793a1 1 0 0 1-1.414 0L10 8.414l-5.293 5.293a1 1 0 0 1-1.414-1.414l6-6a1 1 0 0 1 1.414 0L13.5 9.086L18.586 4zM5 18v3a1 1 0 1 1-2 0v-3a1 1 0 1 1 2 0m5-4a1 1 0 1 0-2 0v7a1 1 0 1 0 2 0zm4 1a1 1 0 0 1 1 1v5a1 1 0 1 1-2 0v-5a1 1 0 0 1 1-1m6-4a1 1 0 1 0-2 0v10a1 1 0 1 0 2 0z" />
              </svg>
              <h2 class="benefit-text-header">High Payout Rates</h2>
              <div>
                <p class="benefit-text-description">We do our best to provide you with the best possible payout rates.</p>
              </div>
            </div>
          </div>

          <div class="benefit b-withdrawal" style="grid-area: b-withdrawal;background-image: url(./images/landing/feature_ad.png);
                background-size: cover;
                background-repeat: no-repeat;
                background-position: bottom;
                background-color: #1c5cb1;">
            <div class="benefit-text">
              <svg width="4rem" height="4rem" viewBox="0 0 24 24">
                <path fill="currentColor" d="M12 12a3 3 0 1 0 3 3a3 3 0 0 0-3-3m0 4a1 1 0 1 1 1-1a1 1 0 0 1-1 1m-.71-6.29a1 1 0 0 0 .33.21a.94.94 0 0 0 .76 0a1 1 0 0 0 .33-.21L15 7.46A1 1 0 1 0 13.54 6l-.54.59V3a1 1 0 0 0-2 0v3.59L10.46 6A1 1 0 0 0 9 7.46ZM19 15a1 1 0 1 0-1 1a1 1 0 0 0 1-1m1-7h-3a1 1 0 0 0 0 2h3a1 1 0 0 1 1 1v8a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1v-8a1 1 0 0 1 1-1h3a1 1 0 0 0 0-2H4a3 3 0 0 0-3 3v8a3 3 0 0 0 3 3h16a3 3 0 0 0 3-3v-8a3 3 0 0 0-3-3M5 15a1 1 0 1 0 1-1a1 1 0 0 0-1 1" />
              </svg>
              <h2 class="benefit-text-header">Low Withdrawal Minimum</h2>
              <div>
                <p class="benefit-text-description">We have a very low withdrawal minimum, currently it's only $1.00. It will not take too long to collect your first payout.</p>
              </div>
            </div>
          </div>

          <div class="benefit b-fast" style="grid-area: b-fast;background-image: url(./images/landing/feature_ad.png);
                background-size: cover;
                background-repeat: no-repeat;
                background-position: bottom;
                background-color: #1c5cb1;">
            <div class="benefit-text">
              <svg width="4rem" height="4rem" viewBox="0 0 24 24">
                <path fill="currentColor" d="m20.38 8.57l-1.23 1.85a8 8 0 0 1-.22 7.58H5.07A8 8 0 0 1 15.58 6.85l1.85-1.23A10 10 0 0 0 3.35 19a2 2 0 0 0 1.72 1h13.85a2 2 0 0 0 1.74-1a10 10 0 0 0-.27-10.44z" />
                <path fill="currentColor" d="M10.59 15.41a2 2 0 0 0 2.83 0l5.66-8.49l-8.49 5.66a2 2 0 0 0 0 2.83" />
              </svg>
              <h2 class="benefit-text-header">Fast Withdrawals</h2>
              <div>
                <p class="benefit-text-description">We always pay our publishers on time. If your account balance has a minimum of $1.00 you can request a withdrawal which will be paid as soon as possible. Mostly during the next few hours as withdrawals are made constantly. Nevetheless in rarely cases it could take up to 48 hours.</p>
              </div>
            </div>
          </div>

          <div class="benefit b-statistics" style="grid-area: b-statistics;background-image: url(./images/landing/about_bg.png);
                background-size: cover;
                background-repeat: no-repeat;
                background-position: bottom;
                background-color: #821cb1;">
            <div class="benefit-text">
              <svg width="4rem" height="4rem" viewBox="0 0 24 24">
                <path fill="currentColor" d="M1.75 17.55q-.325-.225-.387-.612T1.5 16.2l3.775-6.05q.275-.425.763-.463t.837.338L9 12.5l3.175-5.175q.275-.475.838-.475t.862.45L14.9 8.85q.275.425.163.813t-.438.587t-.712.163t-.663-.463l-.175-.25l-3.075 5q-.275.425-.775.475t-.85-.35L6.25 12.35L3.2 17.275q-.225.375-.663.463t-.787-.188m14.325 3.025q-1.875 0-3.187-1.312t-1.313-3.188t1.313-3.187t3.187-1.313t3.188 1.313t1.312 3.187q0 .65-.175 1.263t-.525 1.137l2.4 2.4q.3.3.313.7t-.288.7t-.712.3t-.713-.3l-2.425-2.4q-.5.35-1.112.525t-1.263.175m0-2q1.05 0 1.775-.725t.725-1.775t-.725-1.775t-1.775-.725t-1.775.725t-.725 1.775t.725 1.775t1.775.725M17.3 10.3q-.325-.2-.45-.575t.15-.8L20.8 2.9q.225-.35.65-.45t.775.175q.325.225.4.612t-.125.738l-3.825 6q-.275.425-.663.475t-.712-.15" />
              </svg>
              <h2 class="benefit-text-header">Real Time Statistics</h2>
              <div>
                <p class="benefit-text-description">Once your website or web application is approved by our staff and your ad code is placed, you are ready to monitor all your statistics such as impressions, views, clicks and all the earnings for each country in real time.</p>
              </div>
            </div>
          </div>

          <div class="benefit b-clean" style="grid-area: b-clean;background-image: url(./images/landing/about_bg.png);
                background-size: cover;
                background-repeat: no-repeat;
                background-position: bottom;
                background-color: #821cb1;">
            <div class="benefit-text" style="color: white;">
              <svg width="4rem" height="4rem" viewBox="0 0 16 16">
                <path fill="currentColor" d="M5.398 10.807a1.04 1.04 0 0 0 1.204-.003c.178-.13.313-.31.387-.518l.447-1.373a2.34 2.34 0 0 1 1.477-1.479l1.391-.45a1.045 1.045 0 0 0-.044-1.98l-1.375-.448a2.34 2.34 0 0 1-1.48-1.477l-.452-1.388a1.044 1.044 0 0 0-1.973.017l-.457 1.4a2.34 2.34 0 0 1-1.44 1.45l-1.39.447a1.045 1.045 0 0 0 .016 1.974l1.374.445a2.33 2.33 0 0 1 1.481 1.488l.452 1.391c.072.204.206.38.382.504m.085-7.415l.527-1.377l.44 1.377a3.33 3.33 0 0 0 2.117 2.114l1.406.53l-1.382.447A3.34 3.34 0 0 0 6.476 8.6l-.523 1.378L5.504 8.6a3.34 3.34 0 0 0-.8-1.31a3.4 3.4 0 0 0-1.312-.812l-1.378-.522l1.386-.45a3.36 3.36 0 0 0 1.29-.813a3.4 3.4 0 0 0 .793-1.3m6.052 11.457a.806.806 0 0 0 1.226-.398l.248-.762c.053-.158.143-.302.26-.42c.118-.12.262-.208.42-.26l.772-.252a.8.8 0 0 0-.023-1.52l-.764-.25a1.08 1.08 0 0 1-.68-.678l-.252-.773a.8.8 0 0 0-1.518.01l-.247.762a1.07 1.07 0 0 1-.665.679l-.773.252a.8.8 0 0 0 .008 1.518l.763.247c.16.054.304.143.422.261c.119.119.207.263.258.422l.253.774a.8.8 0 0 0 .292.388m-.913-2.793l-.179-.059l.184-.064a2.09 2.09 0 0 0 1.3-1.317l.058-.178l.06.181a2.08 2.08 0 0 0 1.316 1.316l.195.063l-.18.06a2.08 2.08 0 0 0-1.317 1.32L12 13.56l-.058-.18a2.08 2.08 0 0 0-1.32-1.323" />
              </svg>
              <h2 class="benefit-text-header" style="color: white;">Clean Advertisements</h2>
              <div>
                <p class="benefit-text-description" style="color: white;">We use several scans and other security opportunities to keep all advertisements in our network as secure and clean as possible to keep your users away from malware, viruses and any other fraudent acts. Further every single advertisement is being manually reviewed by our staff before advertisers are able to run any campaigns.</p>
              </div>
            </div>
          </div>

          <div class="benefit b-formats" style="grid-area: b-formats;background-image: url(./images/landing/feature_ad.png);
                background-size: cover;
                background-repeat: no-repeat;
                background-position: bottom;
                background-color: #1c5cb1;">
            <div class="benefit-text" style="color: white;">
              <svg width="4rem" height="4rem" viewBox="0 0 24 24">
                <path fill="currentColor" d="M8.75 13.5a3.25 3.25 0 0 1 3.163 2.5h9.337a.75.75 0 0 1 .102 1.493l-.102.007h-9.337a3.251 3.251 0 0 1-6.326 0H2.75a.75.75 0 0 1-.102-1.493L2.75 16h2.837a3.25 3.25 0 0 1 3.163-2.5m6.5-9.5a3.25 3.25 0 0 1 3.163 2.5h2.837a.75.75 0 0 1 .102 1.493L21.25 8h-2.837a3.251 3.251 0 0 1-6.326 0H2.75a.75.75 0 0 1-.102-1.493L2.75 6.5h9.337A3.25 3.25 0 0 1 15.25 4" />
              </svg>
              <h2 class="benefit-text-header" style="color: white;">Different Ad Formats</h2>
              <div>
                <p class="benefit-text-description" style="color: white;">In your account you can choose from various available advertising formats to publish them on your website or web application. Such as Leaderboard 728x90, Medium Rectangle 300x250, Wide Skyscraper 160x600 or the Landscale 640x320 video format. Depending on the advertising format it's possible to choose between CPC (Cost Per Click), CPM (Cost Per Mille) or CPV (Cost Per View) measurements. Different formats may give you different payout rates.</p>
              </div>
            </div>
          </div>

          <div class="benefit b-secure" style="grid-area: b-secure;background-image: url(./images/landing/feature_ad.png);
                background-size: cover;
                background-repeat: no-repeat;
                background-position: bottom;
                background-color: #1c5cb1;">
            <div class="benefit-text">
              <svg width="4rem" height="4rem" viewBox="0 0 24 24">
                <path fill="currentColor" d="m11.005 2l7.298 2.28a1 1 0 0 1 .702.955V7h2a1 1 0 0 1 1 1v2h-13V8a1 1 0 0 1 1-1h7V5.97l-6-1.876l-6 1.876v7.404a4 4 0 0 0 1.558 3.169l.189.136l4.253 2.9L14.787 17h-4.782a1 1 0 0 1-1-1v-4h13v4a1 1 0 0 1-1 1l-3.22.001c-.387.51-.857.96-1.4 1.33L11.005 22l-5.38-3.668a6 6 0 0 1-2.62-4.958V5.235a1 1 0 0 1 .702-.954z" />
              </svg>
              <h2 class="benefit-text-header">Secure Experience</h2>
              <div>
                <p class="benefit-text-description">Apart from an SSL certificate that protects all your personal data, we use a bunch of other security methods to make your experience as secure as possible.</p>
              </div>
            </div>
          </div>

          <div class="benefit b-friendly" style="grid-area: b-friendly;background-image: url(./images/landing/about_bg.png);
                background-size: cover;
                background-repeat: no-repeat;
                background-position: bottom;
                background-color: #821cb1;">
            <div class="benefit-text">
              <svg width="4rem" height="4rem" viewBox="0 0 24 24">
                <path fill="currentColor" d="M20.205 4.791a5.938 5.938 0 0 0-4.209-1.754A5.906 5.906 0 0 0 12 4.595a5.904 5.904 0 0 0-3.996-1.558a5.942 5.942 0 0 0-4.213 1.758c-2.353 2.363-2.352 6.059.002 8.412L12 21.414l8.207-8.207c2.354-2.353 2.355-6.049-.002-8.416" />
              </svg>
              <h2 class="benefit-text-header">Friendly Support</h2>
              <div>
                <p class="benefit-text-description">If you have any questions or need some help please contact our support team at any time. It would be a pleasure for us to assist you!</p>
              </div>
            </div>
          </div>

        </div>

      </div>

    </div>

    <div class="section faq-section">

      <div class="accordion-container">

        <h1 style="text-align: center;">Publisher FAQ</h1>

        <div class="accordion">
          <div class="accordion-intro">
            <h3>How can I sign up as a publisher?</h3>
          </div>
          <div class="accordion-content">
            <p>To sign up as a publisher, fill out the form on our registration page.</p>
          </div>
        </div>

        <div class="accordion">
          <div class="accordion-intro">
            <h3>Can someone else of my household create an account as well?</h3>
          </div>
          <div class="accordion-content">
            <p>Yes, they can also create an account.</p>
          </div>
        </div>

        <div class="accordion">
          <div class="accordion-intro">
            <h3>What are the requirements for my website or web application to be approved?</h3>
          </div>
          <div class="accordion-content">
            <p>The website or web application should be around for some time, have a clean layout and content. It should not be overfilled with advertisements. It needs to be SSL secured and have a top-level domain. We do not accept brand new websites or web applications with no traffic.</p>
          </div>
        </div>

        <div class="accordion">
          <div class="accordion-intro">
            <h3>Are websites or web applications with any kind of content allowed?</h3>
          </div>
          <div class="accordion-content">
            <p>No. We do not accept websites or web applications with any kind of violence, viruses, malware, drugs, alcohol, child pornography or any other illegal and aggressive content. Further, it must be crypto-oriented.</p>
          </div>
        </div>

        <div class="accordion">
          <div class="accordion-intro">
            <h3>Are websites or web applications with incentive traffic allowed?</h3>
          </div>
          <div class="accordion-content">
            <p>Yes, but it should also have other types of traffic. We will not credit your account for traffic from PTC (Paid To Click), PTP (Paid To Promote), or any other "Paid To" or similar traffic source. We blacklist and ignore traffic from such sources. Nevertheless, it doesn't mean that you are not allowed to arbitrage and redirect CPC (Cost Per Click), CPM (Cost Per Mille), or CPV (Cost Per View) traffic from other advertising networks. Organic traffic from any source is very welcome!</p>
          </div>
        </div>

        <div class="accordion">
          <div class="accordion-intro">
            <h3>What about adult content?</h3>
          </div>
          <div class="accordion-content">
            <p>Adult content is not accepted on our network.</p>
          </div>
        </div>

        <div class="accordion">
          <div class="accordion-intro">
            <h3>Are websites or web applications accepted automatically?</h3>
          </div>
          <div class="accordion-content">
            <p>No. Every website or web application is being approved manually by our staff. If you edit or change it after it has been approved, it gets unapproved and needs to be reviewed by our staff again.</p>
          </div>
        </div>

        <div class="accordion">
          <div class="accordion-intro">
            <h3>How long does it usually take for a website or web application to be approved?</h3>
          </div>
          <div class="accordion-content">
            <p>Websites and web applications are constantly being reviewed. Nevertheless, please allow up to 48 hours for the process.</p>
          </div>
        </div>

      </div>

    </div>

    <div style="
          background-size: cover;
          background-repeat: no-repeat;
          background-position: center;">
      <div id="faq" class="section faq-section">
        <div class="accordion-container">

          <h3 style="text-align: center;">General FAQ</h3>

          <div class="accordion">
            <div class="accordion-intro">
              <h3>Which cryptocurrencies are currently accepted within your service?</h3>
            </div>
            <div class="accordion-content">
              <p>Currently, we use Litecoin for all transactions. If you wish to use another cryptocurrency, please let us know.</p>
            </div>
          </div>

          <div class="accordion">
            <div class="accordion-intro">
              <h3>I have a question which is not listed anywhere below, what should I do?</h3>
            </div>
            <div class="accordion-content">
              <p>In this case, please contact us, and we will be happy to help you!</p>
            </div>
          </div>

          <div class="accordion">
            <div class="accordion-intro">
              <h3>How long does it take for a deposit to be credited?</h3>
            </div>
            <div class="accordion-content">
              <p>Deposits are automatically credited after 1 confirmation on the blockchain. If your balance doesn't update by that time and your deposit gets marked as expired, please let us know your TXID (Transaction ID) and invoice ID via support ticket.</p>
            </div>
          </div>

          <div class="accordion">
            <div class="accordion-intro">
              <h3>What is the minimum deposit amount?</h3>
            </div>
            <div class="accordion-content">
              <p>Our current minimum deposit amount is $1.00.</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  {{-- REGISTER MODAL --}}
  <div class="registration-grid-wrapper" id="register-dialog" style="{{$errors->any() ? 'display:flex;' : ''}}">
    <div class="account-form">
      {{-- CLOSE BUTTON --}}
      <button type="button" style="top: 10px; right: 10px" data-target="register-dialog" onclick="toggleAccountModal(this)" class="register-dialog-close-button">
        <svg width="2rem" height="2rem" viewBox="0 0 1024 1024">
          <path fill="#ffffff" d="M195.2 195.2a64 64 0 0 1 90.496 0L512 421.504L738.304 195.2a64 64 0 0 1 90.496 90.496L602.496 512L828.8 738.304a64 64 0 0 1-90.496 90.496L512 602.496L285.696 828.8a64 64 0 0 1-90.496-90.496L421.504 512L195.2 285.696a64 64 0 0 1 0-90.496" />
        </svg>
      </button>
      {{-- CLOSE BUTTON --}}
      <div id="form-pages">
        <div class="form-page {{ (session('isRegister') == '1' ? 'form-page-active' : '') }}" id="form-page-1">
          <div class="account-form-header">
            <p>Register</p>
            <span>Already have an account?</span>
            <a class="register-button" href="javascript:void(0)" data-target="form-page-2" onclick="formPager('form-page-2', 'form-page-1')">Login</a>
          </div>
          <div style="color: red; margin-bottom: 10px;" class="div-errors">
            @foreach ($errors -> all() as $error)
            <div style="margin-bottom: 10px;">&#10147; {{ $error }}</div>
            @endforeach
          </div>
          <div class="inputs">
            <form method="POST" action="{{ route('register') }}">
              @csrf
              <div class="account-type-selection">
                <label for="account-type--avdertiser">
                  <input type="radio" id="account-type--avdertiser" name="type" value="Advertiser" required><span>Advertiser</span>
                </label>
                <label for="account-type--publisher">
                  <input type="radio" id="account-type--publisher" name="type" value="Publisher" required><span>Publisher</span>
                </label>
              </div>
              <x-input.text name="name" label="<span class='red bold'>*</span>" icon="person" required value="{{ old('name') }}" center="true"
                placeholder="Full Name"
                onfocus="this.placeholder=''"
                onblur="this.placeholder='Full Name'" />
              <x-input.text name="email" label="<span class='red'>*</span>" icon="mail" required value="{{ old('email') }}" center="true"
                placeholder="Email Address"
                onfocus="this.placeholder=''"
                onblur="this.placeholder='Email Address'" />
              <x-input.password name="password" label="<span class='red'>*</span>" icon="password" required center="true"
                placeholder="Password"
                onfocus="this.placeholder=''"
                onblur="this.placeholder='Password'" />
              <x-input.password name="password_confirmation" label="<span class='red'>*</span>" icon="password" required center="true"
                placeholder="Confirm Password (*)"
                onfocus="this.placeholder=''"
                onblur="this.placeholder='Confirm Password'" />


              <?php
              $country_options = \App\Models\Country::visible()->get()->map(fn($country) => ['value' => $country->id, 'caption' => $country->name])->toArray();
              ?>
              <x-input.select name="country_id" value="{{ old('country_id') }}" center="true" label="<span class='red'>*</span>"
                data-live-search="true" data-size="5" :options="$country_options" />


              <x-input.text name="state" label="" icon="password" value="{{ old('state') }}" center="true"
                placeholder="State"
                onfocus="this.placeholder=''"
                onblur="this.placeholder='State'" />
              <x-input.text name="city" label="" icon="password" value="{{ old('city') }}" center="true"
                placeholder="City"
                onfocus="this.placeholder=''"
                onblur="this.placeholder='City'" />
              <x-input.text name="zip" label="" value="{{ old('zip') }}" center="true"
                placeholder="ZIP Code"
                onfocus="this.placeholder=''"
                onblur="this.placeholder='ZIP Code'" />
              <x-input.text name="address" label="" value="{{ old('address') }}" center="true"
                placeholder="Address"
                onfocus="this.placeholder=''"
                onblur="this.placeholder='Address'" />
              <x-input.text name="company" label="" value="{{ old('company') }}" center="true"
                placeholder="Company Name"
                onfocus="this.placeholder=''"
                onblur="this.placeholder='Company Name'" />
              <x-input.text name="phone" label="" value="{{ old('phone') }}" center="true"
                placeholder="Phone Number"
                onfocus="this.placeholder=''"
                onblur="this.placeholder='Phone Number'" />
              <x-input.text name="business_id" label="" value="{{ old('business_id') }}" center="true"
                placeholder="Business ID"
                onfocus="this.placeholder=''"
                onblur="this.placeholder='Business ID'" />
              <x-input.check name="tos" center="true" label="I agree to <a href={{ route('terms-of-service') }} class='reg-tos' style='text-decoration:none'>Terms Of Service!</a>" />
              <div class="captcha-section">
                <div onclick="Ads.Modules.Captcha.reload(this)" title="Reload">
                  {!! captcha_img() !!}
                </div>
                <div class="captcha-container">
                  <input class="{{ $errors->has('captcha') ? 'is-invalid' : '' }}"
                    type="text" name="captcha"
                    placeholder="Enter Captcha"
                    onfocus="this.placeholder=''"
                    onblur="this.placeholder='Enter Captcha'" />
                  @error('captcha')
                  <span class="invalid-feedback" role="alert">Invalid Captcha</span>
                  @enderror
                </div>
              </div>
              <span>Please note that account type selection and fields marked with <span class="red">*</span> are mandatory!</span>
              <button type="submit" class="confirm-button">Register</button>
            </form>
          </div>
        </div>
        <!-- <div class="form-page {{(session('isRegister') ? '' : (session('isForgetPassword') ? 'form-page-active' : ''))}}" id="form-page-2"> -->
        <div class="form-page {{ (session('isRegister') == '2' ? 'form-page-active' : '') }}" id="form-page-2">
          <div class="account-form-header">
            <p>Login</p>
            <span>No account yet?
              <a class="register-button" href="javascript:void(0)" onclick="formPager('form-page-1', 'form-page-2')">Register</a></span>
          </div>
          <div style="color: red; margin-bottom: 10px;" class="div-errors">
            @foreach ($errors -> all() as $error)
            <div style="margin-bottom: 10px;">&#10147; {{ $error }}</div>
            @endforeach
          </div>
          <div class="inputs">
            <form method="POST" action="{{ route('login') }}">
              @csrf
              <x-input.text name="email" label="" required icon="login"
                placeholder="Email Address"
                onfocus="this.placeholder=''"
                onblur="this.placeholder='Email Address'" center="true" />
              <x-input.password name="password" label="" required icon="permissions"
                placeholder="Password"
                onfocus="this.placeholder=''"
                onblur="this.placeholder='Password'" center="true" />
              @if(!isset($_COOKIE['gdpr']) || isset($_COOKIE['gdpr-functionality-cookies']))
              <x-input.check name="remember" label="Remember Me" :checked="!!old('remember')" center="true" />
              @endif
              <div class="login-form-footer">
                <button type="submit" class="confirm-button">Login</button>
                @if (Route::has('password.request'))
                <a href="#" onclick="formPager('form-page-3', 'form-page-2')">Forgot Your Password?</a>
                @endif
              </div>
            </form>
          </div>
        </div>
        <div class="form-page {{ (session('isRegister') == '3' ? 'form-page-active' : '') }}" id="form-page-3">
          <div class="account-form-header">
            <p>Password Recovery</p>
          </div>
          <div style="color: red; margin-bottom: 10px;" class="div-errors">
            @foreach ($errors -> all() as $error)
            <div style="margin-bottom: 10px;">&#10147; {{ $error }}</div>
            @endforeach
          </div>
          <div class="inputs">
            <form method="POST" action="{{ route('password.email') }}">
              @csrf
              <p>
                Forgot your password? No problem. Please enter the email address you registered with
                and we will send you a link that will allow you to reset your password. Please also check your spam folder just in case.</br></br>
                If you don't receive any email after the next 30 minutes please try again or <a href="{{ route('contact.create') }}">contact our support</a>
                and we will help you.
              </p>
              <x-input.text name="email" label="" required icon="mail" center="true " placeholder="Email Address"
                onfocus="this.placeholder=''"
                onblur="this.placeholder='Email Address'" center="true" />
              <div class="login-form-footer">
                <button type="submit" class="confirm-button">Send Recovery Link</button>
              </div>
            </form>
          </div>
        </div>
        <div class="form-page" id="form-page-4">
          <div class="account-form-header">
            <p>Contact Us</p>
          </div>
          @if(Session::has('success'))
          <div class="alert alert-success">
            {{ Session::get('success') }}
          </div>
          @endif
          <form method="POST" action="{{ route('contact.send') }}">
            @csrf
            @if ($errors->any())
            <div class="mb-4 alert alert-danger rounded">
              <div class="font-medium text-red-600" style="text-align:center; font-weight:bold; font-size:16px">Whoops, something went wrong!</div></br>
              @foreach ($errors->all() as $error)
              <ul>
                <li>{{ $error }}</li>
              </ul>
              @endforeach
            </div>
            @endif
            <x-input.text name="email" label="" placeholder="Email Address" icon="mail" required value="{{ old('email') }}" center="true" />
            <div class="form-group">
              <label class="col-md-4 col-form-label pl-0">Category</label>
              <div data-toggle="buttons" class="row btn-group btn-group-toggle" style="padding: 15px">
                <label class="btn btn-white col-md-3" style="color: white">
                  <input type="radio" name="category" id="category" value="Advertisers"
                    autocomplete="off"
                    required {{ old('category')=='Advertisers' ? 'checked' : ''}}>
                  Advertisers
                </label>
                <label class="btn btn-white col-md-3" style="color: white">
                  <input type="radio" name="category" id="category" value="Publishers"
                    autocomplete="off"
                    required {{ old('category')=='Publishers' ? 'checked' : ''}}>
                  Publishers
                </label>
                <label class="btn btn-white col-md-3" style="color: white">
                  <input type="radio" name="category" id="category" value="Billing" autocomplete="off"
                    required {{ old('category')=='Billing' ? 'checked' : ''}}>
                  Billing
                </label>
                <label class="btn btn-white col-md-3" style="color: white">
                  <input type="radio" name="category" id="category" value="Other" autocomplete="off"
                    required {{ old('category')=='Other' ? 'checked' : ''}}>
                  Other
                </label>
              </div>
            </div>
            <x-input.text name="subject" label="" placeholder="Subject" icon="subtitles" value="{{ old('subject') }}" center="true" required />
            <x-input.textarea name="message" label="" placeholder="Message" value="{{ old('message') }}" center="true" required rows="10" />
            <div class="form-group col-md-12 row">
              <div onclick="Ads.Modules.Captcha.reload(this)" class="captcha col-md-4 text-md-right"
                title="Reload">
                {!! captcha_img() !!}
              </div>
              <div class="col-md-6 input-group">
                <input class="form-control {{ $errors->has('captcha') ? 'is-invalid' : '' }}"
                  type="text" name="captcha" placeholder="Enter Captcha" />
                @error('captcha')
                <span class="invalid-feedback" role="alert">Invalid Captcha</span>
                @enderror
              </div>
            </div>
            <div class="form-group row mb-0">
              <div class="col-md-8 offset-md-4">
                <button type="submit" class="btn btn-primary">Send Message</button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
  {{-- REGISTER MODAL --}}
  <?php
    session(['isRegister' => '2']);
  ?>
</x-page-layout>
