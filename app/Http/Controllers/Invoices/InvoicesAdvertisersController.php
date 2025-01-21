<?php

namespace App\Http\Controllers\Invoices;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Currency;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Promo;
use App\Models\User;
use Auth;
use DB;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Log;

class InvoicesAdvertisersController extends Controller {
    public function index(Request $request) {
        $alerts = '';
        $recentPaymentsIds = Payment::select(['payments.id'])
            ->join('users_advertisers', 'users_advertisers.user_id', '=', 'payments.user_id')
            ->whereNull('payments.txid')
            ->whereNull('payments.confirmed_at')
            ->where('payments.created_at', '>=', now()->addMinutes(-1 * config('ads.max_tx_age_for_confirmation')))
            ->groupBy('payments.id')
            ->get()
            ->pluck('id')
            ->toArray();
        if (!empty($recentPaymentsIds)) {
            $alerts = Payment::with('currency')->whereIn('id', $recentPaymentsIds)->get()
                ->map(function (Payment $payment) {
                    if ($payment->currency->isAvailable()) {
                        $wallet = $payment->currency->getWallet(Auth::user());
                    }

                    $wallet ??= 'WARNING! Do not send any funds now!';

                    return self::getAlert($payment, $wallet);
                })
                ->join('');
        }

        return view('layouts.app', [
            'page_title' => 'Manage Invoices',
            'alerts' => $alerts,
            'slot' =>
                view('components.list.list', [
                    'key' => 'unarchived',
                    'header' => $this->listHeader('unarchived'),
                    'body' => $this->list('unarchived', $request)
                ])->render() .
                view('components.list.list', [
                    'key' => 'archived',
                    'header' => $this->listHeader('archived'),
                    'body' => $this->list('archived', $request)
                ])->render()
        ]);
    }

    public function list($key, Request $request) {
        $query = Auth::user()->advertiser->invoices()->select(['invoices.*'])->with('payment')->where('archived', '=', $key === 'archived')->getQuery();
        $invoices = $this->search($query)->orderByDesc('invoices.created_at')->page($request->query->get('page'));
        return view('components.list.body', [
            'url' => route('advertiser.invoices.list', compact('key'), false),
            'query' => json_encode($request->all(), JSON_FORCE_OBJECT),
            'header' => ['Amount', 'Issue Date', 'Confirmed', 'Purpose', 'Actions'],
            'rows' => $invoices->getCollection()->toString(fn($invoice) => $this->listRow($invoice)->render()),
            'pagination' => $invoices->links()
        ]);
    }

    private function search(Builder $q): Builder {
        if ($this->isSearch()) {
            $req = \request();
            $this->whereGt('invoices.amount', $req->amount_gt, $q);
            $this->whereLt('invoices.amount', $req->amount_lt, $q);
            $this->whereGt('invoices.created_at', $req->issue_after, $q);
            $this->whereLt('invoices.created_at', $req->issue_before, $q);
        }
        return $q;
    }

    private function listRow(Invoice $invoice) {
        return view('components.list.row', [
            'id' => $invoice->id,
            'columns' => [
                Helper::amount($invoice->amount),
                $invoice->created_at->format('Y-m-d H:i'),
                $invoice->isPaid() ? '<a class="approved">✔</a>' : '<a class="declined">✘</a>',
                $invoice->title
            ],
            'show' => ['url' => route('advertiser.invoices.show', ['invoice' => $invoice->id], false)],
        ]);
    }

    private function listHeader($key) {
        if ($key === 'archived') {
            $data = [
                'title' => 'Archived Invoices'
            ];
        } else {
            $data = [
                'title' => 'Available Invoices',
                'add' => '<form data-name="add" class="d-none"></form>',
                'add_url' => route('advertiser.invoices.create', absolute: false)
            ];
        }
        $data['search'] = $this->searchForm($key);
        return view('components.list.header', $data);
    }

    public function show(Invoice $invoice) {
        $rows = [
            ['caption' => 'Invoice ID:', 'value' => $invoice->id],
            ['caption' => 'Amount:', 'value' => Helper::amount($invoice->amount)]
        ];

        if (!$invoice->bonus
            && isset($invoice->payment, $invoice->payment->exchange_rate)
            && !empty($invoice->payment->txid)
            && $invoice->archived
        ) {
            $decimals = config('ads.crypto_decimal_places_length');
            $rows[] = [
                'caption' => 'Crypto Amount:',
                'value' => sprintf("%.{$decimals}f", round($invoice->amount * $invoice->payment->exchange_rate, $decimals)) . ' ' . $invoice->payment->currency_id
            ];

        }

        $rows[] = [
            'caption' => 'Issue Date:',
            'value' => $invoice->created_at->format('Y-m-d H:i'),
        ];

        if (!empty($invoice->payment->txid)) {
            $rows[] = [
                'caption' => 'Confirmation Date:',
                'value' => $invoice->isPaid() ? $invoice->payment->confirmed_at->format('Y-m-d H:i') : 'No'
            ];
             $rows[] = ['caption' => 'Purpose:', 'value' => $invoice->title = 'Deposit Funds'];
			 $rows[] = [
				'caption' => 'Transaction ID:',
				'value' => $invoice->payment->txid,
				'full' => true
			];
		}

        return view('components.list.row-details', ['rows' => $rows]);
    }

    private function form() {
        $currencies_options = Currency::active()->get()->toString(function (Currency $currency) {
            if ($currency->isAvailable()) {
                $wallet = $currency->getWallet(Auth::user());
                $bonus = $currency->bonus ? ' - Currency Bonus ' . ($currency->bonus + 0) . '%' : '';
                return $wallet ? Helper::option($currency->id, $currency->id, false, [
                    'data-subtext' => $currency->name . $bonus,
                    'data-wallet' => $wallet,
                    'data-rate' => $currency->exchange_rate
                ]) : '';
            } else {
                return Helper::option($currency->id, $currency->id, false, [
                    'data-subtext' => $currency->name . ' - Not available right now!',
                    'data-wallet' => '',
                    'data-rate' => $currency->exchange_rate,
                    'disabled' => true
                ]);
            }
        });
        return view('components.invoices.advertiser-form', compact('currencies_options'));
    }

    private function searchForm(string $key) {
        return view('components.invoices.advertiser-search-form', compact('key'));
    }

    public function create() {
        return $this->form();
    }

    public function store(Request $request) {
        $errors = [];
        $currency = Currency::active()->find($request->currency);
        if (!$currency) {
            $errors['currency'] = 'Currency is required';
        } elseif (!$currency->isAvailable()) {
            $errors['currency'] = 'Currency is not available now.';
        } elseif (!config('app.debug')) {
            $wallet = $currency->getWallet(Auth::user(), false);
            if (!$wallet || $wallet !== $request->wallet) {
                $errors['form'] = 'There is error with currency and wallet.';
            }
        }
        $amount = (float)$request->amount;
        if ($amount < config('ads.minimum_deposit')) {
            $errors['amount'] = 'Amount is not valid!';
        }
        $promo = null;
        if (isset($request->promo)) {
            $promo = Promo::verifyCode($request->promo);
            if (!$promo) {
                $errors['amount'] = 'Promo code is not valid!';
            }
        }

        if (!empty($errors)) {
            return $this->failure($errors);
        }

        $userId = Auth::id();

        $payment = new Payment;
        $payment->title = 'Deposit Funds';
        $payment->user_id = $userId;
        $payment->currency_id = $currency->id;
        $payment->amount = $amount;
        $payment->exchange_rate = $currency->exchange_rate;

        $invoices = [];
        $invoices[] = Invoice::getInstance($userId, $amount, $request->title ?? $payment->title);
        if (isset($promo)) {
            $invoices[] = Invoice::getInstance($userId, $amount * $promo->bonus / 100, "Promo Bonus $promo->bonus%",
                ['bonus' => true, 'promo' => $promo->id]);
        }
        if (isset($currency->bonus)) {
            $invoices[] = Invoice::getInstance($userId, $amount * $currency->bonus / 100, "Currency Bonus $currency->bonus%", ['bonus' => true]);
        }

        try {
            $invoices = DB::transaction(function () use ($payment, $invoices, $promo) {
                $payment->save();
                $payment->invoices()->saveMany($invoices);
                if ($promo !== null) {
                    $promo->purchase();
                }
                return $invoices;
            });

            $result = join('', array_map(fn($i) => $this->listRow($i)->render(), $invoices));
            $alert = self::getAlert($payment, $request->wallet);
            return $this->success($result, $alert);
        } catch (Exception $e) {
            return $this->exception($e);
        }
    }

    public function verifyPromo(Request $request) {
        $code = $request->input('code');
        if (!$code) {
            return $this->failure(['form' => '<a style="color:red">Promo code not provided.</a>']);
        }

        $promo = Promo::verifyCode($code);
        if ($promo) {
            return $this->success($promo->bonus);
        } else {
            return $this->failure(['form' => '<a style="color:red">Promo code is not valid!</a>']);
        }
    }

    public static function confirmPayments() {
        $recentPaymentsIds = Payment::select(['payments.id'])
            ->join('users_advertisers', 'users_advertisers.user_id', '=', 'payments.user_id')
            ->whereNull('payments.txid')
            ->whereNull('payments.confirmed_at')
            ->where('payments.created_at', '>=', now()->addMinutes(-1 * config('ads.max_tx_age_for_confirmation')))
            ->groupBy('payments.id')
            ->get()
            ->pluck('id')
            ->toArray();
        $recentPayments = Payment::with('currency')->whereIn('id', $recentPaymentsIds)->get();
        $adminId = User::admin()->id;
        foreach ($recentPayments as $payment) {

            $txid = $payment->currency->verifyTransaction($payment->user_id, $payment->amount * $payment->exchange_rate);
            if ($txid !== null) {
                $payment->confirmed_at = now();
                $payment->confirmed_by_id = $adminId;
                $payment->txid = $txid;

                DB::transaction(function () use ($payment) {
                    $payment->save();

                    $payment->invoices()->update([
                        'title' => DB::raw('CONCAT(invoices.title)'),
                        'archived' => true
                    ]);

                    Log::info("Payment#" . $payment->id . ": " . ($payment->amount * $payment->exchange_rate) . " " . $payment->currency_id . " confirmed.");
                });
            }
        }

        // Clean up
        $expiredInvoices = Invoice::join('payments', 'payments.id', '=', 'invoices.payment_id')
            ->join('users_advertisers', 'users_advertisers.user_id', '=', 'payments.user_id')
            ->whereNull('payments.confirmed_at')
            ->where('invoices.archived', '=', false)
            ->where('payments.created_at', '<', now()->addMinutes(-1 * config('ads.max_tx_age_for_confirmation')))
            ->select(['invoices.id', 'invoices.promo'])
            ->get();

        // Give back promo
        $expiredPromos = $expiredInvoices->pluck('promo')->filter()->unique();
        if (!$expiredPromos->isEmpty()) {
            Promo::whereIn('id', $expiredPromos)->update(['purchased' => DB::raw('purchased - 1')]);
        }

        // Archive and update titles
        Invoice::whereIn('id', $expiredInvoices->pluck('id'))
            ->update([
                'title' => DB::raw('CONCAT(title, " - Expired")'),
                'archived' => true
            ]);
    }

    private static function getAlert(Payment $payment, $wallet) {
        $timeLeft = $payment->created_at->timestamp + (config('ads.max_tx_age_for_confirmation') * 60) - time();
        $decimals = config('ads.crypto_decimal_places_length');
        return view('components.invoices.deposit-alert', [
            'base_amount' => Helper::amount($payment->amount),
            'crypto_amount' => sprintf("%.{$decimals}f", round($payment->amount * $payment->exchange_rate, $decimals)) . ' ' . $payment->currency->id,
            'expiry_secs' => $timeLeft,
            'wallet' => $wallet,
            'id' => $payment->id,
            'countdown' => true
        ])->render();
    }
}
