<div class="form-group {{ isset($center) ?  'row' : '' }}">
    <div class="{{ isset($center) ? 'col-md-6 offset-md-4' : '' }}">
        <div class="form-group d-flex">
            <div class="slider-checkbox" id="{{ $name }}_div">
                <input type="checkbox" 
                    {!! $attributes->except(['center', 'label', 'name', 'suffix', 'id'])->class(['is-invalid' => $errors->has($name), 'rel-parent' => isset($related)]) !!} name="{{ $name }}"
                    id="chk_{{ $name.((isset($suffix) ? $suffix : '')) }}" />
                @if ($name == 'cookies_first')
                    <label style="background: darkgreen; cursor: not-allowed" for="chk_{{ $name.((isset($suffix) ? $suffix : '')) }}">
                @else 
                    <label for="chk_{{ $name.((isset($suffix) ? $suffix : '')) }}">
                @endif
                    <span id="ball"></span>
                </label>
            </div>
            <label class="pl-2" for="{{ $name }}_div">{!! $label ?? ucwords($name) !!}</label>
            @error($name)
            <span class="invalid-feedback" role="alert">{{ $message }}</span>
            @enderror
        </div>
    </div>
</div>
