<x-form name="update" method="POST" class="d-flex">
    <x-slot name="action">{{ route($route . '.ads.campaigns.countries.update', compact('campaign'), false) }}</x-slot>
    <x-slot name="method">PUT</x-slot>
    <x-slot name="submit">Ads.Modules.CampaignsCountries.submitForm(this)</x-slot>

    @foreach($tiers as $category => $tier)
        <fieldset class="row col-12">
            <div class="form-check legend">
                <input type="checkbox" id="{{$category}}" onchange="Ads.form.legendToggle(this)"
                        {!! $all[$category] ? 'checked' : '' !!}>
                <label for="{{$category}}">{{$category}}</label>
            </div>
            @foreach(array_chunk($tier, 3) as $columnTiers)
                <div class="col-4">
                    @foreach($columnTiers as $columnTier)
                        <x-input.check name="countries[{{ $columnTier['id'] }}]" label="{{ $columnTier['name'] }}"
                                       :checked="$columnTier['checked']"/>
                    @endforeach
                </div>
            @endforeach
        </fieldset>
    @endforeach
    <fieldset class="row col-12">
        <div class="form-check legend">
            <input type="checkbox" id="Tier 4" onchange="Ads.form.legendToggle(this)" name="tier4"
                    {!! $all['Tier 4'] ? 'checked' : '' !!}>
            <label for="Tier 4">Tier 4: {{$tier4Cost}}</label>
        </div>
    </fieldset>
</x-form>
