<button type="button" class="btn btn-secondary text-uppercase {{ $class ?? '' }}" data-url="{{ $url ?? '' }}"
        onclick="{{ $click ?? 'Ads.Utils.submitAction(this)' }}" data-params="{{ $params ?? '' }}"
        data-confirm="{{ $confirm ?? '' }}">
    {{ $slot }}
</button>
