<div class="alert alert-dismissible fade show mb-0 {{ $class ?? '' }}" role="alert">
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">×</span>
    </button>
    {!! isset($icon) ? '<i class="fa '.$icon.' mx-2"></i>' : '' !!}
    {!! $message !!}
</div>
