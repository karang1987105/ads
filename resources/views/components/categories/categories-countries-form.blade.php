<x-form name="update" method="POST" class="d-flex">
    <x-slot name="action">{{ route('admin.categories.countries.update', compact('category'), false) }}</x-slot>
    <x-slot name="method">PUT</x-slot>
    <x-slot name="submit">Ads.Modules.CampaignsCountries.submitForm(this)</x-slot>

    @foreach($tiers as $category => $tier)
        <fieldset class="row col-12">
            <div class="form-group legend"><label for="{{$category}}">{{$category}}</label></div>
            <div class="row col-12">
                <strong class="col-12 col-sm-3 pt-3 mt-4 text-center">Apply To All</strong>
                <x-input.text name="price[{{ $category }}][all][cpc]" label="CPC" groupClass="col-12 col-sm-3"/>
                <x-input.text name="price[{{ $category }}][all][cpm]" label="CPM" groupClass="col-12 col-sm-3"/>
                <x-input.text name="price[{{ $category }}][all][cpv]" label="CPV" groupClass="col-12 col-sm-3"/>
                <hr class="col-12">
            </div>

            @for($i = 0; $i < count($tier); $i += 1)
                <div class="row col-12">
                    <strong class="col-12 col-sm-3 pt-3 mt-4 text-center">{{ $tier[$i]['name'] }}</strong>
                    <x-input.text name="price[{{ $category }}][{{ $tier[$i]['id'] }}][cpc]" label="CPC"
                                  value="{{ $tier[$i]['cpc'] ?? '' }}" groupClass="col-12 col-sm-3"/>
                    <x-input.text name="price[{{ $category }}][{{ $tier[$i]['id'] }}][cpm]" label="CPM"
                                  value="{{ $tier[$i]['cpm'] ?? '' }}" groupClass="col-12 col-sm-3"/>
                    <x-input.text name="price[{{ $category }}][{{ $tier[$i]['id'] }}][cpv]" label="CPV"
                                  value="{{ $tier[$i]['cpv'] ?? '' }}" groupClass="col-12 col-sm-3"/>
                    <hr class="col-12">
                </div>
            @endfor
        </fieldset>
    @endforeach

    <fieldset class="row col-12">
        <div class="form-group legend"><label for="Tier 4">Tier 4</label></div>
        <div class="row col-12">
            <strong class="col-12 col-sm-3 pt-3 mt-4 text-center">All Other Countries</strong>
            <x-input.text name="price[Tier 4][all][cpc]" label="CPC" groupClass="col-12 col-sm-3"
                          value="{{ $tier4['cpc'] ?? '' }}"/>
            <x-input.text name="price[Tier 4][all][cpm]" label="CPM" groupClass="col-12 col-sm-3"
                          value="{{ $tier4['cpm'] ?? '' }}"/>
            <x-input.text name="price[Tier 4][all][cpv]" label="CPV" groupClass="col-12 col-sm-3"
                          value="{{ $tier4['cpv'] ?? '' }}"/>
            <hr class="col-12">
        </div>
    </fieldset>
</x-form>
