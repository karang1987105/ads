<x-form name="add" method="POST" action="">
    @if(isset($campaign))
        <x-slot name="action">
            {{ route('advertiser.ads.campaigns.update', compact('ad', 'campaign'), false) }}
        </x-slot>
        <x-slot name="method">PUT</x-slot>
        <x-slot name="submit">Ads.item.submitForm(this)</x-slot>
    @elseif($editable)
        <x-slot name="action">
            {{ route('advertiser.ads.campaigns.store', compact('ad'), false) }}
        </x-slot>
        <x-slot name="submit">Ads.list.submitAddForm(this)</x-slot>
    @endif

    @if($editable)
        <x-form-column>
            <x-input.text name="budget" label="Add Balance" :required="!isset($campaign)"/>
        </x-form-column>

        <x-form-column>
            <x-input.select name="category_id" label="Category" required
                            class="trigger-change" onchange="Ads.Modules.CampaignsCountries.changeCategory(this)"
                            :options="$category_options"/>

          <x-input.select name="device" label="Target Device" required
                            value="{{ isset($campaign) ? $campaign->device : '' }}" :options="$devices"/>
        </x-form-column>

        <x-form-column>
            <x-input.check name="proxy" label="VPN Traffic Allowed"
                           :checked="isset($campaign) && $campaign->proxy===true"/>
			<x-input.check name="enabled" label="Start Campaign" :checked="isset($campaign) && $campaign->enabled"/>
		<span>GEO Targeting</span>
        </x-form-column>
    @else
        <x-form-column>
            <x-input.select name="category_id" label="Category" required
                            class="trigger-change" onchange="Ads.Modules.CampaignsCountries.changeCategory(this)"
                            :options="$category_options"/>
        </x-form-column>
        <x-form-column>
            <x-input.select name="device" label="Target Device" required
                            value="{{ isset($campaign) ? $campaign->device : '' }}" :options="$devices"/>
        </x-form-column>
		
        <x-form-column>
            <x-input.check name="proxy" label="VPN Traffic Allowed"
                           :checked="isset($campaign) && $campaign->proxy===true"/>
			<x-input.check name="enabled" label="Start Campaign" :checked="isset($campaign) && $campaign->enabled"/>
		<span>GEO Targeting</span>
		</x-form-column>
	@endif
    @foreach($tiers as $category => $tier)
        <fieldset class="row col-12">
            <div class="form-check legend">
                <input type="checkbox" id="{{$category}}" onchange="Ads.form.legendToggle(this)"
                        {!! $all[$category] ? 'checked' : '' !!}>
                <label for="{{$category}}">{{$category}}</label>
            </div>

            @for($i = 0, $len = count($tier); $i < $len; $i += 1)
                @if ($i % 3 === 0)
                    <div class="col-6 col-md-4 col-lg-3">
                        @endif
                        <x-input.check name="countries[{{ $tier[$i]['id'] }}]"
                                       label="{{ $tier[$i]['name'] }}" :checked="$tier[$i]['checked']"/>
                        @if ($i % 3 === 2)</div>@endif
                    @endfor
                    @if (count($tier) % 3 !== 0)</div>
                @endif
        </fieldset>
    @endforeach
    <fieldset class="row col-12">
        <div class="form-check legend">
            <input type="checkbox" id="Tier 4" onchange="Ads.form.legendToggle(this)" name="tier4"
                    {!! $all['Tier 4'] ? 'checked' : '' !!}>
            <label for="Tier 4">Tier 4</label>
        </div>
			<label>NOTE: Tier 4 contains all other countries which are not listed in Tier 1-3.</label>
    </fieldset>
</x-form>
