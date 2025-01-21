<?php

namespace App\Http\Controllers;

use App\Helpers\Helper;
use App\Models\Currency;
use App\Models\Payment;
use App\Models\WithdrawalRequest;
use App\Services\Currency\CurrencyService;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Log;
use Validator;

class CurrenciesController extends Controller {
    public function index(Request $request) {
        return view('layouts.app', [
            'page_title' => 'Manage Currencies',
            'slot' => view('components.list.list', ['key' => 'all', 'header' => $this->listHeader(), 'body' => $this->list($request)])
        ]);
    }

    public function activate(Currency $currency, $active) {
        if ($active && !$currency->active) {
            $currency->active = true;
        } elseif (!$active && $currency->active) {
            $currency->active = false;
        }
        $currency->save();
        return $this->listRow($currency);
    }

    public function list(Request $request) {
        $currencies = $this->search(Currency::query())->page($request->query->get('page'));
        return view('components.list.body', [
            'url' => route('admin.currencies.list', absolute: false),
            'query' => json_encode($request->all(), JSON_FORCE_OBJECT),
            'header' => ['Name', 'Currency ID', 'Deposit Bonus', 'Exchange Rate', 'Active', 'RPC Status', 'Actions'],
            'rows' => $currencies->getCollection()->toString(fn($currency) => $this->listRow($currency)->render()),
            'pagination' => $currencies->links()
        ]);
    }

    private function search(Builder $q): Builder {
        if ($this->isSearch()) {
            $req = request();
            $this->whereEquals('currencies.id', $req->id, $q);
            $this->whereString('currencies.name', $req->name, $q);
            $this->whereBoolean('currencies.active', $req->active, $q);
        }
        return $q;
    }

    private function listRow(Currency $currency) {
        return view('components.list.row', [
            'id' => $currency->id,
            'columns' => [
                $currency->name,
                $currency->id,
                ($currency->bonus ?? '0') . '%',
                Helper::amount($currency->exchange_rate > 0 ? 1 / $currency->exchange_rate : 0),
                $currency->active ? '<a class="approved">✔</a>' : '<a class="declined">✘</a>',
                $currency->isAvailable() ? '<a style="font-family:monospace; font-weight:bold; color:green">AVAILABLE!</a>' :
                                           '<a style="font-family:monospace; font-weight:bold; color:red">NOT AVAILABLE!</a>'
            ],
            'show' => ['url' => route('admin.currencies.show', ['currency' => $currency->id], false)],
            'edit' => ['url' => route('admin.currencies.edit', ['currency' => $currency->id], false)],
            'delete' => ['url' => route('admin.currencies.destroy', ['currency' => $currency->id], false)],
            'extra' => view('components.list.row-action', [
                'click' => 'Ads.item.updateRow(this)',
                'title' => $currency->active ? 'Disable' : 'Enable',
                'icon' => $currency->active ? 'block' : 'task_alt',
                'url' => route('admin.currencies.activate', ['currency' => $currency->id, 'active' => intval(!$currency->active)], false)
            ])
        ]);
    }

    public function show(Currency $currency) {
        return view('components.list.row-details', ['rows' => [
            ['caption' => 'Currency ID:', 'value' => $currency->id],
            ['caption' => 'Name:', 'value' => $currency->name],
            ['caption' => 'Active:', 'value' => $currency->active ? '<a class="approved">✔</a>' : '<a class="declined">✘</a>'],
            ['caption' => 'Price Tracker ID:', 'value' => $currency->coingecko],
            ['caption' => 'Exchange Rate:', 'value' => Helper::amount($currency->exchange_rate > 0 ? 1 / $currency->exchange_rate : 0)],
            ['caption' => 'Deposit Bonus:', 'value' => ($currency->bonus ?? 0) . '%'],
            ['caption' => 'Currency Blockheight:', 'value' => $currency->rpc_block_count],
            ['caption' => 'RPC Server URL:', 'full' => true, 'value' => $currency->rpc_server]
        ]]);
    }

    private function form(Currency $currency = null) {
        $intervals = [
            ['value' => 60, 'caption' => 'Every Minute'],
            ['value' => 300, 'caption' => 'Every 5 Minutes'],
            ['value' => 600, 'caption' => 'Every 10 Minutes'],
            ['value' => 1800, 'caption' => 'Every 30 Minutes'],
            ['value' => 3600, 'caption' => 'Every Hour'],
            ['value' => 43200, 'caption' => 'Every 12 Hours'],
            ['value' => 86400, 'caption' => 'Every 24 Hours'],
        ];
        return view('components.currencies.form', compact('currency', 'intervals'));
    }

    private function searchForm() {
        return view('components.currencies.search-form');
    }

    private function listHeader() {
        return view('components.list.header', ['title' => 'Existing Currencies', 'add' => $this->form(), 'search' => $this->searchForm()]);
    }

    public function edit(Currency $currency) {
        if ($this->currencyUsed($currency->id)) {
            return view('components.extra-plain', ['slot' => "Currency is being used and can't be edited at this time."]);
        }
        return $this->form($currency);
    }

    public function destroy(Currency $currency) {
        if (!$this->currencyUsed($currency->id)) {
            return $currency->delete();
        }
        return "Currency is being used and can't be edited at this time.";
    }

    private function validateFields(Request $request, array|null $fields = null): \Illuminate\Contracts\Validation\Validator|array {
        $allFields = [
            'id' => 'required|string|max:4',
            'name' => 'required|string',
            'bonus' => 'between:0,100.00',
            'coingecko' => 'required|string',
            'rpc_server' => 'required|string',
            'rpc_block_count_interval' => 'sometimes|required|in:60,300,600,1800,3600,43200,86400',
            'exchange_rate' => 'nullable',
        ];
        if (isset($fields)) {
            $allFields = array_filter($allFields, fn($key) => in_array($key, $fields), ARRAY_FILTER_USE_KEY);
        }
        return Validator::make($request->all(), $allFields);
    }

    public function store(Request $request) {
        $validator = $this->validateFields($request);
        if ($validator->fails()) {
            return $this->failure($validator->errors());
        }

        try {
            $currency = Currency::create([
                'id' => $request->id,
                'name' => $request->name,
                'active' => isset($request->active) ? 1 : 0,
                'coingecko' => $request->coingecko,
                'rpc_server' => $request->rpc_server,
                'exchange_rate' => isset($request->exchange_rate) && $request->exchange_rate > 0 ? 1 / $request->exchange_rate : 0,
            ]);
            return $this->success($this->listRow($currency)->render());

        } catch (Exception $e) {
            return $this->exception($e);
        }
    }

    public function update(Request $request, Currency $currency) {
        $validator = $this->validateFields($request);
        if ($validator->fails()) {
            return $this->failure($validator->errors());
        }

        if ($this->currencyUsed($currency->id)) {
            return $this->failure(['form' => "Currency is being used and can't be edited at this time."]);
        }

        $request['active'] = isset($request->active) ? 1 : 0;
        $request['exchange_rate'] = isset($request->exchange_rate) && $request->exchange_rate > 0 ? 1 / $request->exchange_rate : 0;

        try {
            $currency->update($request->all());
            return $this->success($this->listRow($currency)->render());
        } catch (Exception $e) {
            return $this->exception($e);
        }
    }

    public static function ResetExchangeRates() {
        $rates = CurrencyService::getExchangeRates(Currency::active()->get(['coingecko'])->pluck('coingecko')->join(','));
        $log = [];
        foreach ($rates as $coingecko => $rate) {
            if ($rate > 0) {
                Currency::where('coingecko', $coingecko)->update(['exchange_rate' => 1 / $rate]);
                $log[] = "$coingecko: $" . round(1 / $rate, 8);
            }
        }
        Log::info("Rates updated: " . json_encode($log));
    }

    public static function UpdateBlockCount() {
        Currency::active()
            ->whereNotNull('rpc_block_count_interval')
            ->whereRaw('UNIX_TIMESTAMP() - rpc_block_count_ts >= rpc_block_count_interval')
            ->each(function (Currency $currency) {
                $currentBlockCount = $currency->getService()->getBlockCount();
                if ($currentBlockCount && $currentBlockCount != $currency->rpc_block_count) {
                    $currency->update([
                        'rpc_block_count' => $currentBlockCount,
                        'rpc_block_count_ts' => time()
                    ]);
                }
            });
    }

    private function currencyUsed($id): bool {
        return Payment::query()
                ->where('payments.currency_id', $id)
                ->join('invoices', 'invoices.payment_id', '=', 'payments.id')
                ->join('invoices_campaigns', function ($q) {
                    $q->whereColumn('invoices_campaigns.invoice_id', 'invoices.id')
                        ->whereColumn('invoices_campaigns.amount', '>', 'invoices_campaigns.current');
                })
                ->exists()
            ||
            WithdrawalRequest::query()
                ->where('withdrawals_requests.currency', $id)
                ->join('invoices', function ($q) {
                    $q->whereColumn('invoices.withdrawal_request_id', 'withdrawals_requests.id')
                        ->whereNull('invoices.payment_id')
                        ->where('invoices.archived', 0);
                })
                ->exists()
            ||
            Payment::query()
                ->join('users_advertisers', 'users_advertisers.user_id', '=', 'payments.user_id')
                ->whereNull('payments.txid')
                ->whereNull('payments.confirmed_at')
                ->where('payments.created_at', '>=', now()->addMinutes(-1 * config('ads.max_tx_age_for_confirmation')))
                ->exists();
    }
}
