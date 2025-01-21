<form data-name="{{$name ?? 'search'}}" action="{{$action}}" method="{{$method ?? 'GET'}}"
    {{ $attributes->class('list-form') }}>
    @if (isset($method) && strtoupper($method)==='PUT')
        @method('PUT')
    @endif
    <input type="hidden" name="__search" value="1"/>
    {!! $slot !!}
    <x-form-buttons>
        {!! $buttons ?? '' !!}
        <button onclick="{{ $submit ?? 'Ads.list.submitSearchForm(this)' }}" type="button"
                class="mb-2 btn btn-primary mr-2 submit">{{ $submitLabel ?? 'Search' }}</button>
    </x-form-buttons>
        <input type="submit" name="__submit" class="d-none"/>
</form>
