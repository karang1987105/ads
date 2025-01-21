<x-form name="add" method="POST" enctype="multipart/form-data">
    @isset($ad)
        <x-slot name="action">
            {{ route('advertiser.ads.update', ['ad' => $ad], false) }}
        </x-slot>
        <x-slot name="method">PUT</x-slot>
        <x-slot name="submit">Ads.item.submitForm(this)</x-slot>
    @else
        <x-slot name="action">
            {{ route('advertiser.ads.store', absolute: false) }}
        </x-slot>
        <x-slot name="submit">Ads.list.submitAddForm(this)</x-slot>
    @endisset
    <x-form-column>
        <x-input.select name="ad_type_id" class="rel-parent" label="AD Type"
                        :disabled="isset($ad)" value="{{ isset($ad) ? $ad->ad_type_id : '' }}"
                        onchange="Ads.Modules.Ads.form.relations(this)" :options="$adtype_options"/>
    </x-form-column>
    <x-form-column>
        <div class="form-group">
            <label for="domain_id" class="col-form-label text-md-right">Domain</label>
            <div class="input-group">
                <div class="col-6 px-0">
                    <select class="form-control @error('domain_id') is-invalid @enderror" id="domain_id" required
                            name="domain_id" data-ad-type="Banner,Video">
                        {!! $domain_options !!}
                    </select>
                    @error('domain_id')
                    <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                    @enderror
                </div>
                <div class="col px-0">
                    <input type="text" id="banner[url]" name="banner[url]" autocomplete="off" required
                           data-ad-type="Banner" data-ad-type-no-container="1"
                           value="{{ isset($ad, $ad->banner) ? $ad->banner->url : '/' }}"
                           class="form-control @error('banner[url]') is-invalid @enderror"/>
                    <input type="text" id="video[url]" name="video[url]" autocomplete="off" required
                           data-ad-type="Video" data-ad-type-no-container="1"
                           value="{{ isset($ad, $ad->video) ? $ad->video->url : '/' }}"
                           class="form-control @error('video[url]') is-invalid @enderror"/>
                </div>
            </div>
        </div>
    </x-form-column>
        <x-form-column>
            <x-input.text name="banner[title]" value="{{ isset($ad, $ad->banner) ? $ad->banner->title : '' }}" required
                          data-ad-type="Banner" label="Title"
						  placeholder="Example: CPM Medium Rectangle"
						  onfocus="this.placeholder=''"
						  onblur="this.placeholder='Example: CPM Medium Rectangle'"/>
            <x-input.text name="video[title]" value="{{ isset($ad, $ad->video) ? $ad->video->title : '' }}" required
                          data-ad-type="Video" label="Title"
						  placeholder="Example: CPM Medium Rectangle"
						  onfocus="this.placeholder=''"
						  onblur="this.placeholder='Example: CPM Medium Rectangle'"/>
        </x-form-column>
        <x-form-column>
            <x-input.text name="banner[file]" type="file" data-ad-type="Banner" label="Banner File" :required="!isset($ad)"
						  description="{{'Allowed Files: ' . implode(', ',config('ads.ads.banners.extensions')) . '. ' . 'Maximum Size: ' . round(config('ads.ads.banners.max_size')/1024) . 'MB.' }}"/>
            <x-input.text name="video[file]" type="file" required data-ad-type="Video" label="Video File" :required="!isset($ad)"
                          description="{{'Allowed Files: ' . implode(', ',config('ads.ads.videos.extensions')) . '. ' . 'Maximum Size: ' . round(config('ads.ads.videos.max_size')/1024) . 'MB.' }}"/>
        </x-form-column>
</x-form>
