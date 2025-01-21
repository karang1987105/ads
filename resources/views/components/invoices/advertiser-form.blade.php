<x-form name="add" method="POST">
    <x-slot name="action">
        {{ route('advertiser.invoices.store', absolute: false) }}
    </x-slot>
    <x-slot name="submit">Ads.list.submitAddForm(this)</x-slot>
    <x-form-column>
        <x-input.select name="currency" label="Select Currency" default-option-caption="Nothing Selected"
        :options="$currencies_options" required onchange="Ads.Modules.Invoices.showWalletInfo(this)"/>
        <div class="wallet-info col text-center">
            <h5 class="dynamic-info-container"></h5>
            <canvas class="dynamic-info-container"></canvas>
        </div>
		<div class="w-100 d-block mt-2 text-secondary">Minimum deposit amount:
        <a class="bold green">{{ \App\Helpers\Helper::amount(config('ads.minimum_deposit')) }}</a>
	    </div>
	</x-form-column>
    <x-form-column>
        <div class="row">
            <x-input.text name="amount" required groupClass="col-6" autocomplete="off"
                          onkeyup="Ads.Modules.Invoices.invoicesAmountChange(this)"/>
            <div class="form-group col-6 pl-0">
                <label class="col-form-label text-md-right">&nbsp;</label>
                <div class="input-group"><h3 class="crypto-amount" style="color:green; font-weight:bold">0.00</h3></div>
            </div>
        </div>
        <div class="row">
            <div class="form-group col-6">
                <label for="promo" class="col-form-label text-md-right">Promo Code</label>
                <div class="input-group">
                    <input class="form-control" autocomplete="off" type="text" id="promo" name="promo">
                    &nbsp;
                    <button type="button" onclick="Ads.Modules.Invoices.verifyPromo(this)"
                            class="btn btn-secondary" data-url="{{ route('advertiser.invoices.verify-promo') }}">
                        Verify
                    </button>
                </div>
            </div>
            <div class="form-group col-6 pl-0">
                <label class="col-form-label text-md-right">&nbsp;</label>
                <div class="input-group">
                    <h6 id="promo-profit" class="mt-2"></h6>
                </div>
            </div>
        </div>
        {{--        <x-input.text name="title" label="Comment" placeholder="Max 255 characters"/>--}}
    </x-form-column>

    <div class="modal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Invoice Created</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>You've just created <b>$%amount%</b> invoice.<br>Please make transaction as below in <b>%expiry%
                            minutes</b>:</p>
                    <blockquote class="blockquote text-center"><b>%crypto%</b></blockquote>
                    <blockquote class="blockquote text-center">%wallet%</blockquote>
                    <p></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</x-form>
