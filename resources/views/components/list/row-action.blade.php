<span class="row-action {!! $class ?? '' !!}" data-toggle="tooltip" data-url="{{ $url ?? '' }}"
  data-query="{{ $query ?? '{}' }}"
  {!! isset($click) ? 'onclick="'.e($click).'"' : '' !!} {!! isset($title) ? 'title="'.e($title).'"' : '' !!}>
  <img style="width:25px" src="{{ asset("images/action/$icon") }}" />
</span>