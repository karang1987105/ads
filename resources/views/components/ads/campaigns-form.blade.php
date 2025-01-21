<x-form name="add" method="POST">
    @isset($campaign)
        <x-slot name="action">
            {{ route('admin.ads.campaigns.update', compact('ad', 'campaign'), false) }}
        </x-slot>
        <x-slot name="method">PUT</x-slot>
        <x-slot name="submit">Ads.item.submitForm(this)</x-slot>
    @else
        <x-slot name="action">
            {{ route('admin.ads.campaigns.store', compact('ad'), false) }}
        </x-slot>
        <x-slot name="submit">Ads.list.submitAddForm(this)</x-slot>
    @endisset
    <x-form-column>
        <x-input.text label="Add Balance" name="budget" :required="!isset($campaign)" />
	</x-form-column>
	<x-form-column>
        <x-input.select name="category_id" label="Category" required
                        class="trigger-change" onchange="Ads.Modules.CampaignsCountries.changeCategory(this)"
                        :options="$category_options"/>
	</x-form-column>
	<x-form-column type="right">
        <x-input.select name="device" label="Target Device" required
                        value="{{ isset($campaign) ? $campaign->device : '' }}" :options="$devices"/>
	</x-form-column>
	<x-form-column>
		<x-input.check name="proxy" label="VPN Traffic Allowed"
                       :checked="isset($campaign) && $campaign->proxy===true" suffix="{{ isset($campaign) ? $campaign->id : '' }}"/>
        @if(isset($hasEnabledField))
        <x-input.check name="enabled" label="Start Campaign" :checked="isset($campaign) && $campaign->enabled" suffix="{{ isset($campaign) ? $campaign->id : '' }}"/>
        @endif
    </x-form-column>
    <x-form-column>
        <x-input.text name="revenue_ratio" value="{{ isset($campaign) ? $campaign->revenue_ratio : '1' }}"
                      label="Revenue Ratio" required/>
        {{--        @if(isset($hasStopField))--}}
        {{--            <x-input.check name="stop" :checked="isset($campaign) && !$campaign->isActive()"/>--}}
        {{--        @endif--}}
    </x-form-column>
	<x-form-column>GEO Targeting</x-form-column>
    @foreach($tiers as $category => $tier)
        <fieldset class="row col-12">
            <div class="mt-3 legend">
            <!-- suffix="{{ isset($domain) ? $domain->id : '' }}" -->
                <!-- <input type="checkbox" id="{{$category}}" onchange="Ads.form.legendToggle(this)"
                        {!! $all[$category] ? 'checked' : '' !!}> -->
                        @if ($all[$category])
                            <x-input.check id="{{$category}}" onchange="Ads.form.legendToggle(this)" name="{{$category}}"
                                        label="{{$category}}" :checked="true" suffix="{{ isset($campaign) ? $campaign->id : '' }}"/>
                        @else
                            <x-input.check id="{{$category}}" onchange="Ads.form.legendToggle(this)" name="{{$category}}"
                                            label="{{$category}}" :checked="false" suffix="{{ isset($campaign) ? $campaign->id : '' }}"/>
                        @endif
                <!-- <label for="{{$category}}">{{$category}}</label> -->
            </div>

            @for($i = 0,$iMax = count($tier); $i < $iMax; $i += 1)
                @if ($i % 3 === 0)
                    <div class="col-6 col-md-4 col-lg-3">
                        @endif
                        <x-input.check name="countries[{{ $tier[$i]['id'] }}]"
                                       label="{{ $tier[$i]['name'] }}" :checked="$tier[$i]['checked']" suffix="{{ isset($campaign) ? $campaign->id : '' }}"/>
                        @if ($i % 3 === 2)</div>@endif
                    @endfor
                    @if (count($tier) % 3 !== 0)</div>@endif
        </fieldset>
    @endforeach
    <fieldset class="row col-12">
        <div class="mt-3 legend">
            @if ($all['Tier 4'])
                <x-input.check id="Tier4" onchange="Ads.form.legendToggle(this)" name="tier4"
                            label="" :checked="true" suffix="{{ isset($campaign) ? $campaign->id : '' }}"/>
            @else
                <x-input.check id="Tier4" onchange="Ads.form.legendToggle(this)" name="tier4"
                                label="" :checked="false" suffix="{{ isset($campaign) ? $campaign->id : '' }}"/>
            @endif
            <label for="Tier 4">Tier 4</label>
        </div>
		<label>NOTE: Tier 4 contains all other countries which are not listed in Tier 1-3.</label>
    </fieldset>
</x-form>
