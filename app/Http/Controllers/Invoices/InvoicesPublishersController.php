<?php

namespace App\Http\Controllers\Invoices;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Currency;
use App\Models\Invoice;
use App\Models\WithdrawalRequest;
use App\Notifications\UserUpdate;
use Auth;
use DB;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class InvoicesPublishersController extends Controller {

    private PaymentsPublishersController $paymentsPublishersController;

    private function getPaymentsPublishersController(): PaymentsPublishersController {
        if (!isset($this->paymentsPublishersController)) {
            $this->paymentsPublishersController = new PaymentsPublishersController();
        }
        return $this->paymentsPublishersController;
    }

    public function index(Request $request) {
        return view('layouts.app', [
            'page_title' => 'Manage Payments',
            'slot' =>
                view('components.list.list', [
                    'key' => 'unarchived',
                    'header' => $this->listHeader('unarchived'),
                    'body' => $this->list('unarchived', $request)
                ])->render() .

                view('components.list.list', [
                    'key' => 'withdrawals',
                    'header' => $this->listHeader('withdrawals'),
                    'body' => $this->list('withdrawals', $request)
                ])->render() .

                $this->getPaymentsPublishersController()->index($request)
        ]);
    }

    public function list($key, Request $request) {
        if ($key === 'archived') {
            return $this->getPaymentsPublishersController()->list($request);
        }

        $publisher = Auth::user()->publisher;
        if ($key === 'unarchived') {
            $query = $publisher->invoices()->select(['invoices.*'])->with('payment')->where('archived', '=', false)->getQuery();
            $invoices = $this->search($query, 'invoices')->orderByDesc('invoices.created_at')->page($request->query->get('page'));
            return view('components.list.body', [
                'url' => route('publisher.invoices.list', compact('key'), false),
                'query' => json_encode($request->all(), JSON_FORCE_OBJECT),
                'header' => ['Amount', 'Issue Date', 'Earned From', 'Withdrawal Request', 'Paid', 'Actions'],
                'rows' => $invoices->getCollection()->toString(fn($invoice) => $this->listRow($invoice)->render()),
                'pagination' => $invoices->links()
            ]);
        }

        $requests = $publisher->withdrawalRequests()->orderByDesc('created_at')->page($request->query->get('page'));
        return view('components.list.body', [
            'url' => route('publisher.invoices.list', ['key' => $key], false),
            'query' => json_encode($request->all(), JSON_FORCE_OBJECT),
            'header' => ['Amount', 'Currency', 'Request Date', 'Confirmed', 'Paid', 'Actions'],
            'rows' => $requests->getCollection()->toString(fn($request) => $this->withdrawalListRow($request)->render()),
            'pagination' => $requests->links()
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
                Helper::amount($invoice->amount, 4),
                $invoice->created_at->format('Y-m-d H:i'),
                $invoice->title,				
                $invoice->isWithdrawalRequest() ? '<a class="approved">✔</a>' : '<a class="declined">✘</a>',
                $invoice->isPaid() ? '<a class="approved">✔</a>' : '<a class="declined">✘</a>',
            ],
            'show' => ['url' => route('publisher.invoices.show', ['invoice' => $invoice->id], false)]
        ]);
    }

    private function listHeader($key) {
        $data = [];
        if ($key === 'withdrawals') {
            $data['title'] = 'Withdrawal Requests';
			
        } else {
            $data['title'] = 'Available Payments';
            $data['search'] = $this->searchForm($key);
            $data['actions'] = [['title' => 'Withdrawal Request', 'icon' => 'payments', 'click' => "Ads.list.showForm(this,'withdrawal')"]];			
            $data['slot'] = $this->withdrawalForm()->render();
        }
        return view('components.list.header', $data);
    }

    public function show(Invoice $invoice) {
        $rows = [
            ['caption' => 'Payment ID:', 'value' => $invoice->id],
            ['caption' => 'Archived:', 'value' => $invoice->archived ? '<a class="approved">✔</a>' : '<a class="declined">✘</a>'],
        ];
        if ($invoice->isPaid()) {
            $rows[] = [
                'caption' => 'Paid:',
                'value' => Helper::amount($invoice->payment->amount) . ' ' . ($invoice->payment->currency ? $invoice->payment->currency->id . ' ' : '')
                    . (isset($invoice->payment->confirmed_at) ? 'at ' . $invoice->payment->confirmed_at->format('Y-m-d H:i') : 'Unconfirmed')
            ];
            $rows[] = ['caption' => 'Transaction ID:', 'value' => $invoice->payment->txid];
   
	   } else {
      
			$rows[] = [
			'caption' => 'Earned From:', 'value' => $invoice->title,
			'full' => 'true'
			];
     
	 }
        return view('components.list.row-details', ['rows' => $rows]);
    }

    private function searchForm(string $key) {
        return view('components.invoices.publisher-search-form', compact('key'));
    }

    private function withdrawalForm() {
        $invoices_options = Auth::user()->invoices(false)->select(['invoices.*'])->forWithdrawalRequest()->get()
            ->toString(fn($invoice) => Helper::option($invoice->id, Helper::amount($invoice->amount, 4), null, ['data-amount' => $invoice->amount]));
        $currencies_options = Currency::active()->get()
            ->toString(fn($currency) => Helper::option($currency->id, $currency->id, null, ['data-subtext' => $currency->name, 'data-rate' => $currency->exchange_rate]));
        return view('components.invoices.publisher-withdrawal-form', compact('invoices_options', 'currencies_options'));
    }

    public function withdrawalRequest(string $data, Request $request) {
        if ($request->hasValidSignature()) {
            $data = json_decode(decrypt($data), JSON_OBJECT_AS_ARRAY);
            if ($data) {
                $withdrawalRequest = WithdrawalRequest::query()
                    ->where('user_id', $data['user'])
                    ->findOrFail($data['id']);

                $withdrawalRequest->update(['confirmed_at' => now()->toDateTimeString()]);

                return view('pages.withdrawal-confirmation', [
                    'amount' => $data['amount'],
                    'wallet' => $withdrawalRequest->wallet,
                    'currency' => $withdrawalRequest->currency
                ]);
            }
        }

        return view('pages.withdrawal-confirmation', [
            'error' => 'The link has been expired. Please cancel your 	withdrawal request 
			 and create a new one in order to receive a new confirmation link.'
		]);
    }

    public function withdrawal(Request $request) {
        if (!$request->wallet) {
            return $this->failure(['wallet' => 'Wallet is required.']);
        }
        if (!Currency::active()->where('id', '=', $request->currency)->exists()) {
            return $this->failure(['currency' => 'Currency is not valid.']);
        }
        $invoices = Auth::user()->publisher->invoices(false)->select(['invoices.*'])->forWithdrawalRequest()->whereIn('invoices.id', $request->invoices)->get();
        if ($invoices->isEmpty()) {
            return $this->failure(['invoices' => 'Selected invoices are not valid.']);
        }
        $amount = $invoices->sum('amount');
        if ($amount < config('ads.minimum_withdrawal_amount')) {
            return $this->failure(['form' => 'Selected amount is less than minimum withdrawal request.']);
        }

        $publisher = Auth::user();

        try {
            $withdrawalRequest = DB::transaction(function () use ($publisher, $request, $invoices) {
                $withdrawalRequest = WithdrawalRequest::create(['user_id' => $publisher->id, 'currency' => $request->currency, 'wallet' => $request->wallet]);
                $invoices->each(fn($invoice) => $invoice->update(['withdrawal_request_id' => $withdrawalRequest->id]));
                return $withdrawalRequest;
            });
        } catch (Exception $e) {
            return $this->exception($e);
        }


        $link = \URL::temporarySignedRoute('withdrawal-request',
            now()->addMinutes(config('ads.withdrawal_email_link_max_age')),
            [
                'data' => encrypt(
                    json_encode([
                        'id' => $withdrawalRequest->id,
                        'user' => $publisher->id,
                        'amount' => $amount,
                    ])
                )
            ]
        );

        $publisher->notifyUser(UserUpdate::$TYPE_WITHDRAWAL_CONFIRMATION, [
            'link' => $link,
            'amount' => $amount,
            'wallet' => $request->wallet,
            'currency' => $request->currency,
        ]);

        return $this->success(true, view('components.page-message', [
            'class' => 'alert-success',
            'icon' => 'fa-check',
            'message' => 'Success! Please check your email for confirmation.'
        ])->render());
    }

    private function withdrawalListRow(WithdrawalRequest $withdrawal) {
        return view('components.list.row', [
            'id' => $withdrawal->id,
            'columns' => [
                Helper::amount($withdrawal->invoices->sum('amount')),
                $withdrawal->currency,
                $withdrawal->created_at->format('Y-m-d H:i'),
                $withdrawal->isConfirmed() ? '<a class="approved">✔</a>' : '<a class="declined">✘</a>',
                $withdrawal->isPaid() ? '<a class="approved">✔</a>' : '<a class="declined">✘</a>'
            ],
            'show' => ['url' => route('publisher.withdrawals.withdrawalShow', ['withdrawal' => $withdrawal->id], false)],
            'delete' => ['url' => route('publisher.withdrawals.withdrawalDestroy', ['withdrawal' => $withdrawal->id], false)],
            'extra' => view('components.list.row-action', [
                'click' => 'Ads.item.openExtra(this)',
                'title' => 'Invoices',
                'icon' => 'receipt_long',
                'url' => route('publisher.withdrawals.withdrawalInvoicesIndex', ['withdrawal' => $withdrawal->id], false)
            ])
        ]);
    }

    public function withdrawalShow(WithdrawalRequest $withdrawal) {
        $rows = [
            ['caption' => 'Withdrawal Request ID:', 'value' => $withdrawal->id],
            ['caption' => 'Amount:', 'value' => Helper::amount($withdrawal->invoices->sum('amount'))],
            ['caption' => 'Currency:', 'value' => $withdrawal->currency],
            ['caption' => 'Request Date:', 'value' => $withdrawal->created_at->format('Y-m-d H:i')],
            ['caption' => 'Wallet Address:', 'value' => $withdrawal->wallet, 'full' => true],
        ];
        if ($withdrawal->isConfirmed() && $withdrawal->isPaid()) {
            $rows[] = ['caption' => 'TXID', 'value' => $withdrawal->getPayment()->txid];
        }
        return view('components.list.row-details', [
            'rows' => $rows
        ]);
    }

    public function withdrawalDestroy(WithdrawalRequest $withdrawal) {
        if ($withdrawal->user_id != Auth::id()) {
            abort(403);
        }

        return DB::transaction(function () use ($withdrawal) {
            if (!$withdrawal->isPaid()) {
                $withdrawal->invoices()->update(['withdrawal_request_id' => null]);
            }
            return $withdrawal->delete();
        });
    }

    public function withdrawalInvoicesIndex(WithdrawalRequest $withdrawal, Request $request) {
        if ($withdrawal->user_id != Auth::id()) {
            abort(403);
        }
        return view('components.list.list', [
            'key' => 'invoices',
            'header' => view('components.list.header', ['title' => 'Requested Payments']),
            'body' => $this->withdrawalInvoicesList($withdrawal, $request)
        ]);
    }

    public function withdrawalInvoicesList(WithdrawalRequest $withdrawal, Request $request) {
        if ($withdrawal->user_id != Auth::id()) {
            abort(403);
        }
        $invoices = $withdrawal->invoices()->page($request->query->get('page'));
        return view('components.list.body', [
            'url' => route('publisher.withdrawals.withdrawalInvoicesList', ['withdrawal' => $withdrawal->id], false),
            'query' => json_encode($request->all(), JSON_FORCE_OBJECT),
            'header' => ['Amount', 'Issue Date', 'Earned From', 'Paid'],
            'noAction' => true,
            'rows' => $invoices->getCollection()->toString(fn($invoice) => $this->withdrawalInvoicesListRow($invoice)->render()),
            'pagination' => $invoices->links()
        ]);
    }

    private function withdrawalInvoicesListRow(Invoice $invoice) {
        if ($invoice->user_id != Auth::id()) {
            abort(403);
        }
        return view('components.list.row', [
            'id' => $invoice->id,
            'columns' => [
                Helper::amount($invoice->amount, 4),
                $invoice->created_at->format('Y-m-d H:i'),
				$invoice->title,
                $invoice->isPaid() ? '<a class="approved">✔</a>' : '<a class="declined">✘</a>'
            ]
        ]);
    }
}