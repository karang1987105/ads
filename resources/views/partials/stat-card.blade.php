<div class="col-md-3 col-sm-4 mb-4">
    <div class="stats-small stats-small--1 card card-small h-100">
        <div class="card-body d-flex">
            <div class="d-flex flex-column m-auto">
                <div class="stats-small__data text-center">
                    <span class="stats-small__label text-uppercase"><i class="material-icons mr-1" style="font-size:20px; color:white;">settings</i>{{ $title }}</span>
                    <h6 class="stats-small__value count my-3">{{ $value }}</h6>
                </div>
                @isset($stats)
                    <div class="stats-small__data" style="flex-flow: row">
                        @foreach($stats as $i => $stat)
                            <span class="stats-small__percentage @isset($stat['increase'])stats-small__percentage--{{ $stat['increase'] ? 'increase' : 'decrease' }}@endisset {{ $i > 0 ? 'ml-3' : '' }}">
                            {{ $stat['value'] }}
                        </span>
                        @endforeach
                    </div>
                @endisset
            </div>
        </div>
    </div>
</div>