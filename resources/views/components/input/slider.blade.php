<div class="form-group">
    <label for="{{$name}}" class="col-form-label text-md-right">
        {{ $label ?? ucwords($name) }}
    </label>
    <div class="input-group">
        <input class="form-control {{ $errors->has($name) ? 'is-invalid' : ''}}" type="{{ $type ?? 'text' }}"
               id="{{ $name }}" name="{{ $name }}" {!! $min===$max ? 'disabled value="'.$min.'"' : '' !!}/>
        @error($name)
        <span class="invalid-feedback" role="alert">{{ $message }}</span>
        @enderror
    </div>
    <div id="{{$name}}" class="slider slider-info my-3" data-start="{{$start}}" data-min="{{$min}}" data-max="{{$max}}"
         data-values="{{ $values ?? $min . ',' . ($min+($max-$min)*0.25) . ',' . ($min+($max-$min)*0.5) . ',' . ($min+($max-$min)*0.75) . ',' . $max}}">
        <input type="hidden" class="custom-slider-input" name="{{$name}}">
    </div>
</div>
