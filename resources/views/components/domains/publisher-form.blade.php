<x-form name="add" method="POST">
    @isset($domain)
        <x-slot name="action">
            {{ route('admin.publishers.domains.update', compact('domain'), false) }}
        </x-slot>
        <x-slot name="method">PUT</x-slot>
        <x-slot name="submit">Ads.item.submitForm(this)</x-slot>
    @else
        <x-slot name="action">
            {{ route('admin.publishers.domains.store', ['publisher' => $publisher], false) }}
        </x-slot>
        <x-slot name="submit">Ads.list.submitAddForm(this)</x-slot>
    @endisset
    <x-form-column>
        <div class="form-group">
            <label for="domain" class="col-form-label text-md-right">Domain</label>
            <div class="input-group">
                <input placeholder="Example: domain.tld"
					   onfocus="this.placeholder=''" 
					   onblur="this.placeholder='Example: domain.tld'"				
					   value="{{ isset($domain) ? substr($domain->domain, 8) : '' }}" required
                       class="form-control {{ $errors->has('domain') ? 'is-invalid' : ''}}" type="text" id="domain"
                       name="domain"/>
                @error('domain')
                <span class="invalid-feedback" role="alert">{{ $message }}</span>
                @enderror
            </div>
        </div>
    </x-form-column>
    <x-form-column>
        <x-input.select name="category_id" label="Category"
                        value="{{ isset($domain, $domain->category) ? $domain->category->id : '' }}"
                        :options="$category_options"/>
        <x-input.check name="approve" label="Approve" :checked="isset($domain) && $domain->isApproved()" suffix="{{ isset($domain) ? $domain->id : '' }}"/>
    </x-form-column>
</x-form>
