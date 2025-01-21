<div class="form-group {{ isset($center) ?  'row' : '' }} {{ $groupClass ?? '' }}">
    @if (!isset($label))
        <label for="{{ $name }}" class="{{ isset($center) ?  'col-md-4' : '' }} col-form-label text-md-right">
            {!! $label ?? ucwords($name) !!}
        </label>
        <div class="{{ isset($center) ?  'col-md-6' : '' }} input-group">
        @isset($icon)
            <div class="input-group input-group-seamless {{ $errors->has($name) ? 'is-invalid' : '' }}">
                <span class="input-group-prepend"><span class="input-group-text">
                        <i class="material-icons">{{ $icon }}</i></span></span>
                @endisset
                <select id="{{ $name }}" name="{{ $name }}"
                        {{ $attributes->except(['name', 'options', 'default-option-caption', 'label'])->class(['form-control', 'is-invalid' => $errors->has($name)]) }}>
                    @isset($defaultOptionCaption)
                        <option value="" {{ isset($value)&&$value=='' ? 'selected' : '' }}>
                            {{ $defaultOptionCaption }}
                        </option>
                    @endisset
                    @if(is_array($options))
                        @foreach ($options as $option)
                            {!! \App\Helpers\Helper::option($option['value'], $option['caption'] ?? $option['value'],
                                isset($option['selected']) || (isset($value)&&$value==$option['value']), $option['attributes'] ?? []) !!}
                        @endforeach
                    @else
                        {!! $options !!}
                    @endif
                </select>
                @isset($icon)
            </div>
            @error($name)
            <span class="invalid-feedback" role="alert">{{ $message }}</span>
            @enderror
        @endisset
        </div>
    @else
        @if ($label == '')
            <div class="{{ isset($center) ?  'col-md-6' : '' }} input-group">
            @isset($icon)
                <div class="input-group input-group-seamless {{ $errors->has($name) ? 'is-invalid' : '' }}">
                    <span class="input-group-prepend"><span class="input-group-text">
                            <i class="material-icons">{{ $icon }}</i></span></span>
                    @endisset
                    <select id="{{ $name }}" name="{{ $name }}"
                            {{ $attributes->except(['name', 'options', 'default-option-caption', 'label'])->class(['form-control', 'is-invalid' => $errors->has($name)]) }}>
                        @isset($defaultOptionCaption)
                            <option value="" {{ isset($value)&&$value=='' ? 'selected' : '' }}>
                                {{ $defaultOptionCaption }}
                            </option>
                        @endisset
                        @if(is_array($options))
                            @foreach ($options as $option)
                                {!! \App\Helpers\Helper::option($option['value'], $option['caption'] ?? $option['value'],
                                    isset($option['selected']) || (isset($value)&&$value==$option['value']), $option['attributes'] ?? []) !!}
                            @endforeach
                        @else
                            {!! $options !!}
                        @endif
                    </select>
                    @isset($icon)
                </div>
                @error($name)
                <span class="invalid-feedback" role="alert">{{ $message }}</span>
                @enderror
            @endisset
            </div>
        @else
        <label for="{{ $name }}" class="{{ isset($center) ?  'col-md-4' : '' }} col-form-label text-md-right">
            {!! $label ?? ucwords($name) !!}
        </label>
        <div class="{{ isset($center) ?  'col-md-6' : '' }} input-group">
        @isset($icon)
            <div class="input-group input-group-seamless {{ $errors->has($name) ? 'is-invalid' : '' }}">
                <span class="input-group-prepend"><span class="input-group-text">
                        <i class="material-icons">{{ $icon }}</i></span></span>
                @endisset
                <select id="{{ $name }}" name="{{ $name }}"
                        {{ $attributes->except(['name', 'options', 'default-option-caption', 'label'])->class(['form-control', 'is-invalid' => $errors->has($name)]) }}>
                    @isset($defaultOptionCaption)
                        <option value="" {{ isset($value)&&$value=='' ? 'selected' : '' }}>
                            {{ $defaultOptionCaption }}
                        </option>
                    @endisset
                    @if(is_array($options))
                        @foreach ($options as $option)
                            {!! \App\Helpers\Helper::option($option['value'], $option['caption'] ?? $option['value'],
                                isset($option['selected']) || (isset($value)&&$value==$option['value']), $option['attributes'] ?? []) !!}
                        @endforeach
                    @else
                        {!! $options !!}
                    @endif
                </select>
                @isset($icon)
            </div>
            @error($name)
            <span class="invalid-feedback" role="alert">{{ $message }}</span>
            @enderror
        @endisset
        </div>
        @endif
    @endif
</div>
