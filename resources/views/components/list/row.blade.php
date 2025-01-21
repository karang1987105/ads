<li id="item-{{ $id }}" data-key="{{ $id }}">
    <div class="row-content">
        @for ($i = 0, $len=count($columns); $i < $len; $i += 1)
            <div class="col row-column {{!is_array($columns[$i])?'':$columns[$i]['class']}}">
                {!! !is_array($columns[$i]) ? $columns[$i] : $columns[$i]['content'] !!}
            </div>
        @endfor
        @if(isset($show) || isset($extra) || isset($delete) || isset($edit))
            <div class="col row-actions">
                @isset($extra)
                    {!! is_array($extra) ? join('', $extra) : $extra !!}
                @endisset
                @isset($delete)
                    <x-list.row-action title="Delete" icon="delete_forever" click="Ads.item.deleteRow(this)"
                                       url="{{$delete['url'] ?? ''}}" query="{{$delete['query'] ?? '{}'}}"/>
                @endisset
                @isset($edit)
                    <x-list.row-action title="Edit" icon="border_color" click="Ads.item.openExtra(this)"
                                       url="{{$edit['url'] ?? ''}}" query="{{$edit['query'] ?? '{}'}}"/>
                @endisset
                @isset($show)
                    <x-list.row-action title="Details" icon="settings_overscan" click="Ads.item.openExtra(this)"
                                       url="{{$show['url'] ?? ''}}" query="{!! ($show['query'] ?? '{}') !!}"/>
                @endisset
            </div>
        @endif
    </div>
    <div class="row-extra">
        <span data-toggle="tooltip" class="extra-close" onclick="Ads.item.closeExtra(this)" title="Close">
            <i class="material-icons">disabled_by_default</i>
        </span>
        <div class="extra-content"></div>
    </div>
    <div class="overlay @isset($disabled) disabled @endisset"></div>
</li>

