<option
    @isset($_attributes) @foreach($_attributes as $key => $val) {!! ' '.$key.'="'.str_replace('"', '\\"', trim($val)).'"' !!} @endforeach @endisset
    {{ isset($attributes) ? $attributes->except(['selected']) : '' }}
    {{ isset($selected) && $selected===true ? 'selected' : '' }}
    value="{{ $value }}">
    {{ $caption ?? $value }}
</option>
