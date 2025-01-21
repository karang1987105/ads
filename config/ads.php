<?php
return [
    // Base URL of website if the app does NOT locate in server root.
    'base_url' => env('BASE_URL', ''),

    // Number of results in dashboard lists.
    'default_results_per_page' => 15,

    // Default country if app is set to debug mode.
    'localhost_country' => 'US',

    // Default HTTP client options.
    'http_client_options' => [
    'verify' => '/var/www/cacert.pem'
    ],

    // Database column length for crypto amount is: 00000000.00000000.
    // This setting determines the crypto amount length after the decimal point.
    'crypto_decimal_places_length' => 4,

    // Exchange rates reset every x seconds. Possible values: 30sec, 1min, 5min.
    // Note: Set before artisan schedule.
    'exchange_rates_cache_age' => '30sec',

    // Timer in which a deposit must be confirmed to be considered as valid.
    'max_tx_age_for_confirmation' => 30,

    // Minimum number of confirmations for a deposit to be considered as valid.
    'min_tx_confirmations' => 1,

    // While confirming transaction and checking invoice amount with transaction amount, +/- of invoice_amount*ratio
    // is accepted $amount = InvoiceAmount * InvoiceCryptoRatio rounded to config.crypto_decimal_places_length
    // decimal points $tx_amount = TransactionAmount from daemon rounded to config.crypto_decimal_places_length
    // decimal points ABS($amount - $tx_amount) should be lower or equals to config.max_tx_amount_tolerance_ratio
    // @see app/Services/Currency/CurrencyService::verifyTransaction().
    'max_tx_amount_tolerance_ratio' => 0,

    // Minimum allowed deposit for advertisers in USD.
    'minimum_deposit' => 1,

    // Minimum allowed withdrawal for publisher in USD.
    'minimum_withdrawal_amount' => 1,

    // Max age of withdrawal link in minutes.
    'withdrawal_email_link_max_age' => 60,

    // CPM costs divided to {cpm_mile_size} in each trigger. Recommended default value should be 1.
    'cpm_mile_size' => 1,

    // {max_requests} request(s) allowed in {in_minutes} minute(s) for same footprint-ip on events of ads.
    // default: 1 cpc/cpm/cpv allowed for a single client in 10 minutes.
    'footprint_throttle' => [
    'max_requests' => 1,
    'in_minutes' => 10,
    // Max milliseconds between client time and ip time.
    'max_timezone_tolerance' => 5 * 60,
    ],

    // vpnapi.io and proxycheck.io keys to check Proxy/VPN requests.
    'vpnapi_key' => env('VPNAPI_IO_KEY'),
    'proxycheck_api_key' => env('PROXY_CHECK_API_KEY'),

    // Time in minutes to block a visitor from a blacklisted referrer.
    'blocked_referrer_expiry' => 5,

    // Store and check blocked referrer IP via database.
    'blocked_referrer_database_check' => true,

    // Email setting.
    'email' => [
    'from' => [
    'name' => 'CoinADS',
    'email' => 'noreply@coinads.live'
    ],
    'reply_to' => [
    'name' => null,
    'email' => null
    ],
    ],

    // Ads settings, all values called runtime.
    'ads' => [
    // Minutes to possible load of duplicate ad for an ip.
    'throttle' => 1 * 0.25,
    // Allowed extensions to upload for banners.
    'banners' => [
    'extensions' => ['jpeg', 'jpg', 'png', 'gif'],
    // Maximum banner size (KB) allowed to upload banners.
    'max_size' => 2048
    ],
    // Allowed extensions to upload for videos.
    'videos' => [
    // Allowed extensions to upload for videos.
    'extensions' => ['mp4', 'm4v', 'mkv', 'flv'],
    // Maximum banner size (KB) allowed to upload videos.
    'max_size' => 40960,
    // Milliseconds of playing video before impressions and clicks are registered.
    // It is strongly recommended to set the same value for both.
    'player_impression_delay' => 5000,
    'player_click_delay' => 5000,
    'video_ad_event_delay'=>7000,
    // Video ads player type. Possible values: html5, vast.
    'player_type' => 'vast',
    // Loop all videos or not.
    'loop' => true
    ]
    ],

    // Required fields for Managers.
    'managers' => [
    'required_fields' => [
    'company' => false,
    'business_id' => false,
    'phone' => false,
    'country_id' => true,
    'state' => false,
    'city' => false,
    'zip' => false,
    'address' => false,
    ]
    ],

    // Required fields for Advertisers.
    'advertisers' => [
    'required_fields' => [
    'company' => false,
    'business_id' => false,
    'phone' => false,
    'country_id' => true,
    'state' => false,
    'city' => false,
    'zip' => false,
    'address' => false,
    ]
    ],

    // Required fields for Publishers.
    'publishers' => [
    'required_fields' => [
    'company' => false,
    'business_id' => false,
    'phone' => false,
    'country_id' => true,
    'state' => false,
    'city' => false,
    'zip' => false,
    'address' => false,
    ]
    ],

    // Crypto Daemon settings.
    'daemon' => [
    'connection' => [
    // HTTP headers for connecting to Daemon RPC.
    'headers' => [],
    // Maximum seconds for RPC methods call.
    'timeout' => 3
    ],

    // Daemon RPC commands. There are four commands used in the app: getaddressesbylabel, getnewaddress,
    // listtransactions and getblockcount. If any of these commands are different for a daemon, then the
    // function below needs to be used to rewrite these commands. If behaviour of these commands
    // (responses and processes) are different for a daemon, CurrencyService.php should be extended as instruction there.
     'coins' => [
    // Sample:
    // 'COIN-ID' => [
    // 'getaddressesbylabel' => 'COMMAND_NAME_FOR_GETTING_ADDRESSES_BY_LABEL',
    // 'getnewaddress' => 'COMMAND_NAME_FOR_GENERATING_NEW_ADDRESS',
    // 'listtransactions' => 'COMMAND_NAME_FOR_GETTING_TRANSACTIONS_BY_LABEL',
    // 'getblockcount ' => 'COMMAND_NAME_FOR_GETTING_BLOCK_COUNT',
    // ],
     'CRW' => [
     'getaddressesbylabel' => 'getaddressesbyaccount',
     'getnewaddress' => 'getnewaddress',
     'listtransactions' => 'listtransactions'
     ],
     'UGD' => [
     'getaddressesbylabel' => 'getaddressesbyaccount',
     'getnewaddress' => 'getnewaddress',
     'listtransactions' => 'listtransactions'
     ]
]
]
];
