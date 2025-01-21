<div class="form-group {{ isset($center) ? 'row' : '' }} {{ isset($legend) ? 'legend' : '' }} {{ $groupClass ?? '' }}">
    <label for="{{ $name }}"
           class="{{ isset($center) ?  'col-md-4' : '' }} col-form-label text-md-right">{!! $label ?? ucwords($name) !!}</label>
    <div class="{{ isset($center) ?  'col-md-12' : '' }} input-group {{ $inputGroupClass ?? '' }}">
        @isset($icon)
            <div class="input-group input-group-seamless {{ $errors->has($name) ? 'is-invalid' : '' }}">
                <span class="input-group-prepend"><span class="input-group-text">
                <img style="margin-left:-5px; width:25px; position:absolute;" src="{{ asset("images/action/$icon.png") }}" />
                </span></span>
                @endisset
                <input
                {{ $attributes->except(['center', 'label', 'icon', 'name', 'type'])->class(['form-control', 'is-invalid' => $errors->has($name), 'rel-parent' => isset($related)]) }}
                type="{{ $type ?? 'text' }}" id="{{ $name }}" name="{{ $name }}"
                @if(!empty($disabled)) disabled @endif @if(!empty($required)) required @endif/>
                @error($name)
                <span class="invalid-feedback" role="alert">{{ $message }}</span>
                @enderror
                @isset($icon)
            </div>
        @endisset
    </div>
    {!! isset($description) ? '<small class="form-text text-muted">'.$description.'</small>' : '' !!}
</div>