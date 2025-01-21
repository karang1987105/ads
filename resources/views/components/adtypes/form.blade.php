<x-form name="add" method="POST">
    @isset($adType)
        <x-slot name="action">{{ route('admin.ad-types.update', ['ad_type' => $adType], false) }}</x-slot>
        <x-slot name="method">PUT</x-slot>
        <x-slot name="submit">Ads.item.submitForm(this)</x-slot>
    @else
        <x-slot name="action">{{ route('admin.ad-types.store', absolute: false) }}</x-slot>
        <x-slot name="submit">Ads.list.submitAddForm(this)</x-slot>
    @endisset
    <x-form-column>
        <x-input.text name="name" required value="{{ isset($adType) ? $adType->name : '' }}"/>
    </x-form-column>
    <x-form-column type="left">
        <x-input.select name="type" required value="{{ isset($adType) ? $adType->type : '' }}" class="rel-parent"
                        onchange="Ads.Modules.Ads.form.relations(this)"
                        :options="[['value'=>'Banner','attributes'=>['data-type'=>'Banner']],['value'=>'Video']]"/>
        <div class="form-group">
            <label for="kind" class="col-form-label text-md-right">Kind</label>
            <div class="input-group" data-ad-type="Banner">
                <div data-toggle="buttons" class="col btn-group btn-group-toggle mb-3">
                    <label class="btn btn-white {{isset($adType) && $adType->kind==='CPM'?'active':''}}">
                        <input type="radio" name="kind" id="kind" value="CPM"
                                {{isset($adType) && $adType->kind=='CPM'?'checked':''}}>
                        <a style="font-weight:bold; font-size:16px">CPM</a>
                    </label>
                    <label class="btn btn-white {{isset($adType) && $adType->kind==='CPC'?'active':''}}">
                        <input type="radio" name="kind" id="kind" value="CPC"
                                {{isset($adType) && $adType->kind=='CPC'?'checked':''}}>
                        <a style="font-weight:bold; font-size:16px">CPC</a>
                    </label>
                </div>
            </div>
        </div>
    </x-form-column>
    <x-form-column type="right">
        <x-input.select name="device" required
                        value="{{ isset($adType) ? $adType->device : '' }}" label="Target Device"
                        :options="[['value'=>'Mobile'], ['value'=>'Desktop'], ['value'=>'All']]"/>
        <x-input.text name="width" value="{{ isset($adType) ? $adType->width : '' }}" label="Width" required/>
        <x-input.text name="height" value="{{ isset($adType) ? $adType->height : '' }}" label="Height" required/>
        <x-input.check name="active" label="Enable" :checked="isset($adType) && $adType->active" suffix="{{ isset($adType) ? $adType->id : '' }}"/>
    </x-form-column>
</x-form>
