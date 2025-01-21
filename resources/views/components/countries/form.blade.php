<x-form name="move" method="POST">
    <x-slot name="action">{{ route('admin.countries.update', compact('country'), false) }}</x-slot>
    <x-slot name="method">PUT</x-slot>
    <x-slot name="submit">Ads.item.submitForm(this)</x-slot>

    <div class="col-sm-12 col-md-6">
        <div class="form-group">
            <label for="type" class="col-form-label text-md-right">Type</label>
            <div class="input-group">
                <div data-toggle="buttons" class="col btn-group btn-group-toggle mb-3">
                    <label class="btn btn-white {{ $country->category=='Tier 1' ? 'active' : ''}}">
                        <input type="radio" name="category" id="category" value="Tier 1" autocomplete="off"
                               required {{ $country->category=='Tier 1' ? 'checked' : ''}}>
                        Tier 1
                    </label>
                    <label class="btn btn-white {{ $country->category=='Tier 2' ? 'active' : ''}}">
                        <input type="radio" name="category" id="category" value="Tier 2" autocomplete="off"
                               required {{ $country->category=='Tier 2' ? 'checked' : ''}}>
                        Tier 2
                    </label>
                    <label class="btn btn-white {{ $country->category=='Tier 3' ? 'active' : ''}}">
                        <input type="radio" name="category" id="category" value="Tier 3" autocomplete="off"
                               required {{ $country->category=='Tier 3' ? 'checked' : ''}}>
                        Tier 3
                    </label>
                    <label class="btn btn-white {{ $country->category=='Tier 4' ? 'active' : ''}}">
                        <input type="radio" name="category" id="category" value="Tier 4" autocomplete="off"
                               required {{ $country->category=='Tier 4' ? 'checked' : ''}}>
                        Tier 4
                    </label>
                </div>
            </div>
        </div>
    </div>
</x-form>
