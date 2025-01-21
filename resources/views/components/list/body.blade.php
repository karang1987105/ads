<div class="list-body" data-url="{{ $url ?? ''}}" data-query="{{ $query ?? '{}'}}">
    <div class="card-body p-0 pb-3 text-center">
        <ul class="list-rows">
            @isset($header)
                <li class="header">
                    <div class="row-content">
                        @foreach($header as $col)
                            @if(isset($sorting) && in_array($col, $sorting['columns'], true))
                                @if($sorting['current'] === $col)
                                    @if($sorting['desc'])
                                        <div class="col row-column" data-sorting="{{ $col }}">
                                            {{ $col }}<i class="material-icons">north</i>
                                        </div>
                                    @else
                                        <div class="col row-column" data-sorting="{{ $col }}"
                                             data-sorting-desc="1">
                                            {{ $col }}<i class="material-icons">south</i>
                                        </div>
                                    @endif
                                @else
                                    <div class="col row-column" data-sorting="{{ $col }}">
                                        {{ $col }}<i class="material-icons">swap_vert</i>
                                    </div>
                                @endif
                            @else
                                <div class="col row-column">
                                    {{ $col }}
                                </div>
                            @endif
                        @endforeach
                        @if(!empty($noAction))
                            <div class="row-column"></div>
                        @endif
                    </div>
                </li>
            @endisset
            {!! $rows !!}
        </ul>
    </div>
    @isset($pagination)
        <nav>
            <ul class="pagination">
                {!! $pagination !!}
            </ul>
        </nav>
    @endisset
    <div class="overlay"></div>
</div>
