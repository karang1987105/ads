@component('vendor.mail.html.layout')
    {{-- Header --}}
    @slot('header')
    @component('vendor.mail.html.header', ['url' => config('app.url')])
            {{ config('app.name') }}
        @endcomponent
    @endslot

    {{-- Body --}}
    {!! $body !!}

    {{-- Footer --}}
    @slot('footer')
        @component('vendor.mail.html.footer')
            © {{ date('Y') }} {{ config('app.name') }}. @lang('All rights reserved.')
        @endcomponent
    @endslot
@endcomponent
