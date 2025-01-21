<div class="list-header card-header border-bottom">
    <div class="list-actions clearfix">
        <h6 class="m-0">{{ $title }}</h6>
        @isset($add)
            <span class="list-action"
                  onclick="Ads.list.showForm(this, 'add'{!! (isset($add_url) ? ", '$add_url'" : "") !!})"
                  title="{{ $add_title ?? 'Add New' }}"
                  data-toggle="tooltip">
                        <i class="material-icons">add_box</i></span>
        @endisset
        @isset($search)
            <span class="list-action" onclick="Ads.list.showForm(this, 'search')" data-toggle="tooltip"
                  title="{{ $search_title ?? 'Search' }}"><i
                        class="material-icons">search</i></span>
        @endisset
        @isset($actions)
            @foreach($actions as $action)
                <span class="list-action {{ isset($action['disabled']) ? 'disabled' : '' }}" onclick="{{ $action['click'] }}" title="{{ $action['title'] ?: '' }}"
                      data-toggle="tooltip"{!! isset($action['url']) ? 'data-url="' .$action['url']. '"' : '' !!}>
                    <i class="material-icons">{{ $action['icon'] }}</i>
                </span>
            @endforeach
        @endisset
        @if(!isset($refresh) || $refresh === true)
            <span class="list-action" onclick="Ads.list.refresh(this)" title="Refresh" data-toggle="tooltip">
                        <i class="material-icons">refresh</i></span>
        @endif
        <div class="list-loader" role="status"><span class="sr-only">Loading...</span></div>
        <span class="list-close" onclick="Ads.list.closeForm(this)" title="Close" data-toggle="tooltip">
					<i class="material-icons" icon="close">disabled_by_default</i></span>

    </div>
    {!! $add ?? ''!!}
    {!! $search ?? ''!!}
    {!! $slot ?? '' !!}
</div>
