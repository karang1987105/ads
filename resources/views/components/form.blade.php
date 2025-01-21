<form data-name="{{$name}}" action="{{$action}}" {{ $attributes->class('list-form') }}
method="{{isset($method) && strtoupper($method)==='GET' ? 'GET' : 'POST'}}">
    @if(!isset($withoutCSRF))
        @csrf
    @endif
    @if (isset($method) && strtoupper($method)==='PUT')
        @method('PUT')
    @endif
    <div class="form-progress">
        <div></div>
    </div>
    {!! $slot !!}
    @isset($submit)
        <x-form-buttons>
            {!! $buttons ?? '' !!}
            <button onclick="{{ $submit }}" type="button" class="mb-2 btn btn-primary mr-2 submit">
                {{ $submitLabel ?? 'Submit' }}
            </button>
        </x-form-buttons>
    @endisset
    <input type="submit" name="__submit" class="d-none"/>
</form>
