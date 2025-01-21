<div class="form-group {{ isset($center) ?  'row' : '' }}">
    <label for="{{ $name }}" class="{{ isset($center) ?  'col-md-4' : '' }} col-form-label text-md-right">
        {{ $label ?? ucwords($name) }}
    </label>
    <div class="{{ isset($center) ?  'col-md-12' : '' }} input-group">
        <textarea id="{{ $name }}" name="{{ $name }}"
                  {{ $attributes }} class="form-control @error($name) is-invalid @enderror">{!! $value ?? '' !!}</textarea>
        @error($name)
        <span class="invalid-feedback" role="alert">{{ $message }}</span>
        @enderror
    </div>
    {!! isset($description) ? '<small class="form-text text-muted">'.$description.'</small>' : '' !!}
</div>
