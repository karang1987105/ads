<div class="list" data-key="{{ $key }}">
    <div class="col">
        <div class="card card-small mb-4">
            @isset($header)
                {!! $header !!}
            @else
                @component('components.list.header')
                    @slot('title', $title)
                    @isset($add)
                        @slot('add', $add)
                    @endisset
                    @isset($search)
                        @slot('search', $search)
                    @endisset
                    @isset($actions)
                        @slot('actions', $actions)
                    @endisset
                @endcomponent
            @endisset
            {!! !empty($charts) ? '<div class="charts row mx-0">' . $charts .'</div>' : '' !!}
            @if(isset($body))
                {!! $body !!}
            @elseif(!isset($nobody))
                @component('components.list.body')
                    @slot('rows', $rows)
                    @isset($url)
                        @slot('url', $url)
                    @endisset
                    @isset($query)
                        @slot('query', $query)
                    @endisset
                    @isset($header)
                        @slot('header', $header)
                    @endisset
                    @isset($pagination)
                        @slot('pagination', $pagination)
                    @endisset
                @endcomponent
            @endif
        </div>
    </div>
</div>
