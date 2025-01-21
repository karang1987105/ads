<div {{ $attributes->class(empty($type) || $type=='left' ? 'col-sm-12 col-md-6' : ($type=='right' ? 'col-sm-12 col-md-6 offset-sm-12 offset-md-6' : 'col-12')) }}>
    {{ $slot }}
</div>
