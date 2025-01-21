<x-form name="add" method="POST">
    @isset($domain)
        <x-slot name="action">
            {{ route($route.'.domains.update', compact('domain'), false) }}
        </x-slot>
        <x-slot name="method">PUT</x-slot>
        <x-slot name="submit">Ads.item.submitForm(this)</x-slot>
    @else
        <x-slot name="action">
            {{ route($route.'.domains.store', absolute: false) }}
        </x-slot>
        <x-slot name="submit">Ads.list.submitAddForm(this)</x-slot>
    @endisset
    <x-form-column>
        <div class="form-group">
            <label for="domain" class="col-form-label text-md-right">Domain</label>
            <div class="input-group">
                <input placeholder="Example: domain.tld" onfocus ="this.placeholder=''"
					   onblur="this.placeholder='Example: domain.tld'" value="{{ isset($domain) ? substr($domain->domain, 8) : '' }}" required
                       class="form-control {{ $errors->has('domain') ? 'is-invalid' : ''}}" type="text" id="domain"
                       name="domain"/>
                @error('domain')
                <span class="invalid-feedback" role="alert">{{ $message }}</span>
                @enderror
            </div>
        </div>
    </x-form-column>
</x-form>
