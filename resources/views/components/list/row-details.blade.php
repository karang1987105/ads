<ul class="details">
    @foreach($rows as $row)
        <li {{ isset($row['full']) ? 'class=full' : 'class=partial' }}>
            <span>{{ $row['caption'] }}</span>{!! $row['value'] !!}</li>
    @endforeach
</ul>
{!! $slot ?? '' !!}
