<x-form name="withdrawal" method="POST">
    <x-slot name="action">
        {{ route('publisher.invoices.withdrawal', [], false) }}
    </x-slot>
    <x-slot name="submit">Ads.list.submitForm(this, 'POST', () => Ads.list.refresh(this), true)</x-slot>
    <x-form-column>
    <x-input.select name="invoices[]" label="Select Payments" value="" multiple required
		onchange="Ads.Modules.Invoices.invoicesSelectChange(this)" :options="$invoices_options"/>
    <x-input.select name="currency" label="Select Currency" default-option-caption="Nothing Selected" :options="$currencies_options"
		required onchange="Ads.Modules.Invoices.currencySelectChange(this)"/>
    <div class="w-100 d-block mt-2 text-secondary">
     Minimum Withdrawal Amount: <a class="bold green">{{ \App\Helpers\Helper::amount(config('ads.minimum_withdrawal_amount')) }}</a>
    </div>
	</x-form-column>
    <x-form-column>
        <x-input.text name="wallet" label="Wallet Address" placeholder="Example: H8WqCSCPUHvNqNNMPt9YLYkLGsf69rGUoN"
					  onfocus="this.placeholder=''"
					  onblur="this.placeholder='Example: H8WqCSCPUHvNqNNMPt9YLYkLGsf69rGUoN'" required/>
        <div class="form-group col-6 pl-0">
            <label for="amount" class="col-form-label text-md-right">Total Withdrawal Amount</label>
            <div class="input-group">
                <h3 class="total-amount" style="color:green; font-weight:bold">$0.00</h3>
                <h5 class="crypto-amount ml-2 mt-2" style="color:green; font-weight:bold"></h5>
            </div>
        </div>
    </x-form-column>
</x-form>