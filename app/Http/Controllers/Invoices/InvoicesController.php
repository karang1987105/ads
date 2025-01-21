<?php

namespace App\Http\Controllers\Invoices;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Currency;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\User;
use App\Models\WithdrawalRequest;
use App\Notifications\UserUpdate;
use Arr;
use Auth;
use Carbon\Carbon;
use DB;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Query\JoinClause;

class InvoicesController extends Controller {
    public function indexUsers(Request $request) {
        $slots = '';
        if (self::checkPermission('advertisers', self::ANY)) {
            $slots .= view('components.list.list', [
                'key' => 'advertisers',
                'header' => view('components.list.header', [
                    'title' => 'Advertisers',
                    'search' => $this->usersSearchForm('advertisers'),
                    'actions' => []
                ]),
                'body' => $this->listUsers('advertisers', $request)
            ])->render();
        }
        if (self::checkPermission('publishers', self::ANY)) {
            $slots .= view('components.list.list', [
                'key' => 'publishers',
                'header' => view('components.list.header', [
                    'title' => 'Publishers',
                    'search' => $this->usersSearchForm('publishers'),
                    'actions' => []
                ]),
                'body' => $this->listUsers('publishers', $request)
            ])->render();
        }
        return view('layouts.app', [
            'page_title' => 'Manage Invoices',
            'slot' => $slots
        ]);
    }

    public function listUsers($key, Request $request) {
        self::requirePermission($key, self::ANY);

        $scope = $key === 'advertisers' ? 'asAdvertiser' : 'asPublisher';
        $query = User::query()
            ->leftJoin('invoices AS i', 'i.user_id', 'users.id')
            ->select('users.id')
            ->when(
                $key === 'advertisers',
                function (Builder $q) {
                    $q->addSelect([
                        DB::raw('SUM(IFNULL(balances.balance,0)) AS balance'),
                        DB::raw('SUM(IFNULL(paid_amounts.total_paid,0)) AS total_paid'),
                    ]);
                    $q->leftJoinSub(
                        DB::table('invoices')
                            ->select(['invoices.id', 'invoices.user_id'])
                            ->addSelect(DB::raw('IF(MAX(invoices.amount)<0 OR MAX(invoices.amount)>SUM(IFNULL(invoices_campaigns.amount,0)), MAX(invoices.amount)-SUM(IFNULL(invoices_campaigns.amount,0)), 0) AS balance'))
                            ->leftJoin('invoices_campaigns', 'invoices_campaigns.invoice_id', 'invoices.id')
                            ->join('payments', function (JoinClause $join) {
                                $join->on('payments.id', 'invoices.payment_id');
                                $join->whereNotNull('payments.confirmed_at');
                            })
                            ->groupBy(['invoices.id', 'invoices.user_id']),
                        'balances',
                        'balances.id',
                        'i.id'
                    );
                    $q->leftJoinSub(
                        DB::table('invoices')
                            ->select(['invoices.id', 'invoices.user_id'])
                            ->addSelect(DB::raw('SUM(invoices.amount) AS total_paid'))
                            ->join('payments', function (JoinClause $join) {
                                $join->on('payments.id', 'invoices.payment_id');
                                $join->whereNotNull('payments.confirmed_at');
                            })
                            ->groupBy(['invoices.id', 'invoices.user_id']),
                        'paid_amounts',
                        'paid_amounts.id',
                        'i.id'
                    );
                },
                function (Builder $q) {
                    $q->addSelect([
                        DB::raw('SUM(IFNULL(invoices.balance,0)) AS balance'),
                        DB::raw('SUM(IFNULL(invoices.total_paid,0)) AS total_paid'),
                        DB::raw('MAX(invoices.has_withdrawal_request)=1 AS has_withdrawal_request'),
                    ]);
                    $q->leftJoinSub(
                        DB::table('invoices')
                            ->select(['invoices.id', 'invoices.user_id'])
                            ->addSelect(DB::raw('MAX(invoices.amount) * MAX(payments.confirmed_at IS NULL) AS balance'))
                            ->addSelect(DB::raw('MAX(invoices.amount) * MAX(payments.confirmed_at IS NOT NULL) AS total_paid'))
                            ->addSelect(DB::raw('MAX(payments.confirmed_at IS NULL) AND MAX(invoices.withdrawal_request_id IS NOT NULL) AS has_withdrawal_request '))
                            ->leftJoin('payments', 'payments.id', 'invoices.payment_id')
                            ->groupBy(['invoices.id', 'invoices.user_id']),
                        'invoices',
                        'invoices.id',
                        'i.id'
                    );
                },
            )
            ->active()
            ->$scope()
            ->groupBy('users.id')
            ->when($request->query->get('sorting'), function ($q, $sorting) use ($request) {
                $sorting = match ($sorting) {
                    'Current Balance' => 'balance',
                    'Total Paid' => 'total_paid',
                };
                return $q->orderBy($sorting, $request->query->has('sorting_desc') ? 'desc' : 'asc');
            });

        $users = $this->searchUsers($query)
            ->page($request->query->get('page'));

        $items = $users->getCollection()->keyBy('id')->toArray();
        $models = User::whereIn('id', array_keys($items))->get()->keyBy('id');
        $rows = array_reduce($items, function (string $carry, $item) use ($items, $models) {
            $ad = $models[$item['id']];
            $additionalData = [];
            foreach ($items[$ad->id] as $key => $value) {
                if ($key !== 'id') {
                    $additionalData[$key] = $value;
                }
            }

            $carry .= $this->userListRow($ad, $additionalData)->render();
            return $carry;
        }, '');

        $headers = ['Name', 'Current Balance', 'Total Paid'];
        if ($key === 'publishers') {
            $headers[] = 'Withdrawal Request';
        }
        $headers[] = 'Actions';

        return view('components.list.body', [
            'url' => route('admin.invoices.listUsers', compact('key'), false),
            'query' => json_encode($request->all(), JSON_FORCE_OBJECT),
            'header' => $headers,
            'rows' => $rows,
            'pagination' => $users->links(),
            'sorting' => [
                'columns' => ['Current Balance', 'Total Paid'],
                'current' => $request->query->get('sorting'),
                'desc' => $request->query->has('sorting_desc')
            ],
        ]);
    }

    private function searchUsers(Builder $q): Builder {
        if ($this->isSearch()) {
            $req = \request();
            $this->whereEquals('users.id', $req->user, $q);
            if ($req->input('withdrawal')) {
                $q->whereHas('invoices', fn($qq) => $qq->whereNotNull('invoices.withdrawal_request_id'));
            }
        } else {
            $q->whereHas('invoices');
        }
        return $q;
    }

    private function userListRow(User $user, array $additionalData) {
        $columns = [
            $user->name,
            Helper::amount($additionalData['balance'], $user->isAdvertiser() ? 2 : 4),
            Helper::amount($additionalData['total_paid'])
        ];
        if ($user->isPublisher()) {
            $columns[] = $additionalData['has_withdrawal_request'] ?
			'<i class="material-icons text-danger">warning</i>' : '<a class="declined">✘</a>';
        }
        return view('components.list.row', [
            'id' => $user->id,
            'columns' => $columns,
            'extra' => view('components.list.row-action', [
                'click' => 'Ads.item.openExtra(this)',
                'title' => 'Invoices',
                'icon' => 'receipt_long',
                'url' => route('admin.invoices.index', ['user' => $user->id], false)
            ])
        ]);
    }

    private function usersSearchForm(string $key) {
        $users_options = User::active()->{$key === 'advertisers' ? 'asAdvertiser' : 'asPublisher'}()->get()->toString(fn($user) => Helper::option($user->id, "$user->name"));
        return view('components.invoices.admin-user-search-form', compact('key', 'users_options'));
    }

    //////////////////////////////////////////////

    public function index(User $user, Request $request) {
        self::requirePermission(self::getPermissionSubject($user), self::ANY);

        $view = view('components.list.list', [
            'key' => $user->type . '-invoices',
            'header' => $this->listHeader($user),
            'body' => $this->list($user, $request)
        ])->render();

        if ($user->isPublisher() && self::checkPermission('publishers', self::WITHDRAWAL_REQUESTS)) {
            $view .= '<div id="withdrawals_' . $user->id . '" class="d-none"></div>';
        }

        return $view;
    }

    public function list(User $user, Request $request) {
        self::requirePermission(self::getPermissionSubject($user), self::ANY);

        $query = Invoice::select(['invoices.*'])
            ->leftjoin('payments', 'payments.id', '=', 'invoices.payment_id')
            ->where('invoices.user_id', '=', $user->id)
            ->orderBy(DB::raw('payments.confirmed_at IS NULL'), 'desc')
            ->orderBy('invoices.created_at', 'desc');

        $invoices = $this->search($query)->orderByDesc('invoices.created_at')->page($request->query->get('page'));

        $header = ['Amount', 'Issue Date', 'Paid'];
        if ($user->isPublisher()) {
            $header[] = 'Withdrawal Request';
        }
        $header[] = 'Actions';

        return view('components.list.body', [
            'url' => route('admin.invoices.list', ['user' => $user->id], false),
            'query' => json_encode($request->all(), JSON_FORCE_OBJECT),
            'header' => $header,
            'rows' => $invoices->getCollection()->toString(fn($invoice) => $this->listRow($user, $invoice)->render()),
            'pagination' => $invoices->links()
        ]);
    }

    private function listRow(User $user, Invoice $invoice) {
        $columns = [
            Helper::amount($invoice->amount, $user->isAdvertiser() ? 2 : 4),
            $invoice->created_at->format('Y-m-d H:i'),
            $invoice->isPaid() ? '<a class="approved">✔</a>' : '<a class="declined">✘</a>'
        ];
        if ($user->isPublisher() && self::checkPermission('publishers', self::WITHDRAWAL_REQUESTS)) {
            $columns[] = $invoice->isWithdrawalRequest() ? (!$invoice->isPaid() ?
			'<i class="material-icons text-danger">warning</i>' : '<a class="approved">✔</a>') : '<a class="declined">✘</a>';

        }
        return view('components.list.row', [
            'id' => $invoice->id,
            'columns' => $columns,
            'show' => ['url' => route('admin.invoices.show', ['invoice' => $invoice->id], false)]
        ]);
    }

    private function search(Builder $q): Builder {
        if ($this->isSearch()) {
            $req = \request();
            $this->whereGt('invoices.amount', $req->amount_gt, $q);
            $this->whereLt('invoices.amount', $req->amount_lt, $q);
            $this->whereGt('invoices.created_at', $req->issue_after, $q);
            $this->whereLt('invoices.created_at', $req->issue_before, $q);
            $this->whereNotNull('invoices.withdrawal_request_id', $req->withdrawal, $q);
            if ($req->boolean('paid')) {
                $q->paid();
            }
        }
        return $q;
    }

    private function listHeader(User $user) {
        $data = [
            'title' => 'Invoices',
            'search' => $this->searchForm($user),
            'actions' => []
        ];
        if (self::checkPermission(self::getPermissionSubject($user), [self::ADD_FUND, self::REMOVE_FUND])) {
            $data['add'] = $this->addFundForm($user)->render();
            $data['add_title'] = 'User Balance';
        }

        if ($user->isPublisher() && self::checkPermission('publishers', self::WITHDRAWAL_REQUESTS)) {
            $id = 'withdrawals_' . $user->id;
            $data['actions'][] = [
                'title' => 'Withdrawal Requests',
                'icon' => 'payments',
                'click' => "Ads.Modules.Invoices.showWithdrawalRequests(this, $('#$id'), '" . route('admin.withdrawals.withdrawalsIndex', $user->id, absolute: false) . "')"
            ];
        }
        return view('components.list.header', $data);
    }

    public function show(Invoice $invoice) {
        self::requirePermission(self::getPermissionSubject($invoice->user), self::ANY);

        $rows = [
            ['caption' => 'Invoice ID:', 'value' => $invoice->id],
            ['caption' => 'Amount:', 'value' => Helper::amount($invoice->amount)]
			];

      
		
        $rows[] = ['caption' => 'Comment:', 'value' => $invoice->title
			];
        $rows[] = [
            'caption' => 'Paid:',
            'value' => $invoice->isPaid() ? '<a class="approved">✔</a>' : '<a class="declined">✘</a>'];
		$rows[] = [
            'caption' => 'Issued By:',
            'full' => true,
			'value' => (isset($invoice->title) && isset($invoice->createdBy)) ?
			"{$invoice->createdBy->user->name} at " . $invoice->created_at->format('Y-m-d H:i') : "$invoice->title at " . $invoice->created_at->format('Y-m-d H:i')];
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
		$rows[] = ['caption' => 'Transaction ID:', 'full' => true, 'value' => $invoice->payment->txid
			];
        }		
        return view('components.list.row-details', ['rows' => $rows]);
    }

    private function addFundForm(User $user) {
        $minimum = null;
        if ($user->isAdvertiser()) {
            $minimum = Helper::amount($user->advertiser->getBalance() * -1);
        }
        return view('components.invoices.admin-adfund-form', compact('user', 'minimum'));
    }

    private function searchForm(User $user) {
        $currencies_options = Currency::active()->get()
            ->toString(fn($currency) => Helper::option($currency->id, $currency->name . ' [' . $currency->id . ']'));
        return view('components.invoices.admin-search-form', compact('user', 'currencies_options'));
    }

    public function store(User $user, Request $request) {
        if ($request->amount >= 0 && !self::checkPermission(self::getPermissionSubject($user), self::ADD_FUND)) {
            return $this->failure(['amount' => 'You don\'t have permission to add fund']);
        }

        if ($request->amount < 0 && !self::checkPermission(self::getPermissionSubject($user), self::REMOVE_FUND)) {
            return $this->failure(['amount' => 'You don\'t have permission to remove fund']);
        }

        $validator = Validator::make($request->all(), [
            'amount' => 'required' . ($user->isAdvertiser() ? '|gte:' . ($user->advertiser->getBalance() * -1) : '')
        ]);
        if ($validator->fails()) {
            return $this->failure($validator->errors());
        }

        $publisherCredits = [];
        if ($request->amount < 0 && $user->isPublisher()) {
            $publisherCredits = $user->publisher->invoices(false)->get(['invoices.*']);
            if ($publisherCredits->sum('amount') + $request->amount < 0) {
                return $this->failure(['amount' => 'There is no enough credits to reduce.']);
            }
        }

        $invoice = new Invoice;
        $invoice->title = $request->title ?? '';
        $invoice->user_id = $user->id;
        $invoice->amount = $request->amount;
        $invoice->created_by_id = Auth::id();

        $payment = null;
        if ($user->isAdvertiser() || ($user->isPublisher() && $invoice->amount < 0)) {
            $invoice->archived = true;
            $invoice->title = trim($invoice->title);

            $payment = new Payment;
            $payment->title = $invoice->title;
            $payment->user_id = $user->id;
            $payment->amount = $invoice->amount;
            $payment->exchange_rate = 1;
            $payment->confirmed_at = Carbon::now()->format('Y-m-d H:i:s');
            $payment->confirmed_by_id = Auth::id();
        }

        try {
            $invoice = DB::transaction(function () use ($payment, $invoice, $publisherCredits) {
                if (isset($payment)) {
                    $payment->save();
                    $invoice->payment_id = $payment->id;
                }
                $invoice->save();

                // Edit current balance to cover negative invoice!!!
                if (!empty($publisherCredits)) {
                    $reducing = abs($payment->amount);
                    foreach ($publisherCredits as $credit) {
                        if ($reducing > 0) {
                            $credit->title = substr(trim("Reduced from $credit->amount (Invoice ID $invoice->id) " . $credit->title), 0, 255);
                            if ($reducing >= $credit->amount) {
                                $reducing -= $credit->amount;
                                $credit->amount = 0;
                                // Mark zero amount invoice as paid and archived
                                $credit->archived = true;
                                $credit->payment_id = $payment->id;
                            } else {
                                $credit->amount -= $reducing;
                                $reducing = 0;
                            }
                            $credit->save();
                        }
                    }
                }

                return $invoice;
            });
            return $this->success($this->listRow($user, $invoice)->render());
        } catch (Exception $e) {
            return $this->exception($e);
        }
    }

    ////////////////////////////////////////////////////

    public function withdrawalsIndex(User $user, Request $request) {
        if (self::checkPermission('publishers', self::WITHDRAWAL_REQUESTS) && $user->isPublisher()) {
            return view('components.list.list', [
                'key' => 'withdrawals',
                'header' => view('components.list.header', ['title' => 'Withdrawal Requests', 'search' => $this->withdrawalsSearchForm($user)]),
                'body' => $this->withdrawalsList($user, $request)
            ])->render();
        }
        abort(403);
    }

    public function withdrawalsList(User $user, Request $request) {
        self::requirePermission('publishers', self::WITHDRAWAL_REQUESTS);

        $query = $user->publisher->withdrawalRequests()->orderByDesc('withdrawals_requests.created_at')->getQuery();
        $requests = $this->withdrawalsSearch($query)->page($request->query->get('page'));
        return view('components.list.body', [
            'url' => route('admin.withdrawals.withdrawalsList', ['user' => $user->id], false),
            'query' => json_encode($request->all(), JSON_FORCE_OBJECT),
            'header' => ['Amount', 'Currency', 'Issue Date', 'Confirmed', 'Paid', 'Actions'],
            'rows' => $requests->getCollection()->toString(fn($request) => $this->withdrawalListRow($request)->render()),
            'pagination' => $requests->links()
        ]);
    }

    private function withdrawalsSearch(Builder $q): Builder {
        if ($this->isSearch()) {
            $req = \request();
            $this->whereEquals('withdrawals_requests.id', $req->id, $q);
            $this->whereGt('withdrawals_requests.created_at', $req->issue_after, $q);
            $this->whereLt('withdrawals_requests.created_at', $req->issue_before, $q);
            $this->whereNotNull('invoices.withdrawal_request_id', $req->withdrawal, $q);

            if ($req->exists('confirmed')) {
                $this->whereNotNull('withdrawals_requests.confirmed_at', $req->boolean('confirmed'), $q);
            }

            if ($req->exists('paid')) {
                $q->whereHas('invoices', function ($qq) use ($req) {
                    $this->whereNotNull('invoices.payment_id', $req->boolean('paid'), $qq);
                });
            }
        }
        return $q;
    }

    private function withdrawalsSearchForm(User $user) {
        return view('components.invoices.admin-withdrawals-search-form', compact('user'));
    }

    private function withdrawalListRow(WithdrawalRequest $withdrawal, $disabled = null) {
        $allCount = $withdrawal->invoices->count();
        $paidCount = $withdrawal->invoices->filter(fn($i) => $i->isPaid())->count();
        $unpaidCount = $allCount - $paidCount;
        $extra = [
            view('components.list.row-action', [
                'click' => 'Ads.item.openExtra(this)',
                'title' => 'Invoices',
                'icon' => 'receipt_long',
                'url' => route('admin.withdrawals.withdrawalInvoicesIndex', ['withdrawal' => $withdrawal->id], false)
            ])->render()
        ];
		    if ($withdrawal->isConfirmed()) {
            if ($unpaidCount > 0) {
                $extra[] = view('components.list.row-action', [
                    'click' => "Ads.item.openExtra(this)",
                    'title' => 'Withdraw',
                    'icon' => 'payments',
                    'url' => route('admin.withdrawals.withdrawalForm', ['withdrawal' => $withdrawal->id], false)
                ])->render();
            }
        } else {
            $extra[] = view('components.list.row-action', [
                'click' => "Ads.item.updateRow(this, 'Are you sure that you want to confirm it manually?')",
                'title' => 'Confirm',
                'icon' => 'task_alt',
                'url' => route('admin.withdrawals.confirm', ['withdrawal' => $withdrawal->id], false)
            ])->render();
        }
        $totalAmount = $withdrawal->invoices->sum('amount');
        $unpaidAmount = $withdrawal->invoices->filter(fn($i) => !$i->isPaid())->sum('amount');
        return view('components.list.row', [
            'id' => $withdrawal->id,
            'columns' => [
                ($paidCount != 0 && $unpaidCount != 0 ? Helper::amount($unpaidAmount) . ' / ' : '') . Helper::amount($totalAmount),
				$withdrawal->currency,
                $withdrawal->created_at->format('Y-m-d H:i'),
                $withdrawal->isConfirmed() ? '<a class="approved">✔</a>' : '<a class="declined">✘</a>',
                $paidCount == 0 ? '<a class="declined">✘</a>' : ($paidCount == $allCount ? '<a class="approved">✔</a>' : $paidCount . ' of ' . $allCount)
            ],
            'show' => ['url' => route('admin.withdrawals.withdrawalShow', ['withdrawal' => $withdrawal->id], false)],
            'delete' => ['url' => route('admin.withdrawals.withdrawalDestroy', ['withdrawal' => $withdrawal->id], false)],
            'extra' => $extra,
            'disabled' => $disabled
        ]);
    }

    public function withdrawalShow(WithdrawalRequest $withdrawal) {
        self::requirePermission('publishers', self::WITHDRAWAL_REQUESTS);

        $allCount = $withdrawal->invoices->count();
        $paidCount = $withdrawal->invoices->filter(fn($i) => $i->isPaid())->count();
        $unpaidCount = $allCount - $paidCount;
        $totalAmount = $withdrawal->invoices->sum('amount');
        $unpaidAmount = $withdrawal->invoices->filter(fn($i) => !$i->isPaid())->sum('amount');
        $currency = Currency::find($withdrawal->currency);
        $exchangeRate = $currency !== null ? $currency->exchange_rate : 0;
        $decimals = config('ads.crypto_decimal_places_length');
        $items = [
            ['caption' => 'Withdrawal ID:', 'value' => $withdrawal->id],
            ['caption' => 'Amount:', 'value' => ($paidCount != 0 && $unpaidCount != 0 ? Helper::amount($unpaidAmount) . ' / ' : '') . Helper::amount($totalAmount)],
			['caption' => 'Issue Date:', 'value' => $withdrawal->created_at->format('Y-m-d H:i')],            
            ['caption' => 'Crypto Amount:',
               'value' => ($paidCount != 0 && $unpaidCount != 0 ? sprintf("%.{$decimals}f", round($unpaidAmount * $exchangeRate, $decimals)) . ' / ' : '') .
			   sprintf("%.{$decimals}f", round($totalAmount * $exchangeRate, $decimals)) . ' ' . $withdrawal->currency]
        ];

        if ($paidCount > 0) {
            $items[] = ['caption' => 'Transaction ID:', 'value' => $withdrawal->getPayment()->txid];
        }

        $items[] = ['caption' => 'Wallet Address:', 'full' => true, 'value' => $withdrawal->wallet, 'full' => true];

        return view('components.list.row-details', [
            'rows' => $items
        ]);
    }

    public function withdrawalForm(WithdrawalRequest $withdrawal) {
        self::requirePermission('publishers', self::WITHDRAWAL_REQUESTS);

        return view('components.invoices.admin-pay-form', compact('withdrawal'));
    }

    public function withdrawalDestroy(WithdrawalRequest $withdrawal) {
        self::requirePermission('publishers', self::WITHDRAWAL_REQUESTS);

        return DB::transaction(function () use ($withdrawal) {
            if (!$withdrawal->isPaid()) {
                $withdrawal->invoices()->update(['withdrawal_request_id' => null]);
            }
            return $withdrawal->delete();
        });
    }

    public function withdrawal(WithdrawalRequest $withdrawal) {
        self::requirePermission('publishers', self::WITHDRAWAL_REQUESTS);

        $txid = \request('txid');
        if (!$txid) {
            return $this->failure(['form' => 'TXID is required.']);
        }

        $currency = Currency::active()->find($withdrawal->currency);
        if ($currency === null) {
            return $this->failure(['form' => 'Currency is not valid.']);
        }

        $invoices = $withdrawal->invoices()->select(['invoices.*'])->notPaid()->get();

        $this->withdraw($withdrawal->user, $invoices, $txid, $currency);

        $withdrawal->refresh();

        $withdrawal->user->notifyUser(UserUpdate::$TYPE_WITHDRAWAL_PAID, [
            'amount' => round($invoices->sum('amount'), 2),
            'wallet' => $withdrawal->wallet,
            'currency' => $withdrawal->currency,
        ]);

        $row = $this->withdrawalListRow($withdrawal, true)->render();

        // Delete the object to be removed from list on next refresh
        WithdrawalRequest::where('id', $withdrawal->id)->delete();

        return $this->success($row);
    }

    public function confirm(WithdrawalRequest $withdrawal) {
        self::requirePermission('publishers', self::WITHDRAWAL_REQUESTS);

        if (!$withdrawal->isConfirmed()) {
            $withdrawal->update(['confirmed_at' => now()->toDateTimeString()]);
            $withdrawal->refresh();
        }

        $invoices = $withdrawal->invoices()->select(['invoices.*'])->notPaid()->get();

        $withdrawal->user->notifyUser(UserUpdate::$TYPE_WITHDRAWAL_CONFIRMED, [
            'amount' => round($invoices->sum('amount'), 2),
            'wallet' => $withdrawal->wallet,
            'currency' => $withdrawal->currency,
        ]);

        return $this->withdrawalListRow($withdrawal);
    }

    public function withdrawalInvoicesIndex(WithdrawalRequest $withdrawal, Request $request) {
        self::requirePermission('publishers', self::WITHDRAWAL_REQUESTS);

        return view('components.list.list', [
            'key' => 'invoices',
            'header' => view('components.list.header', ['title' => 'Payments']),
            'body' => $this->withdrawalInvoicesList($withdrawal, $request)
        ]);
    }

    public function withdrawalInvoicesList(WithdrawalRequest $withdrawal, Request $request) {
        self::requirePermission('publishers', self::WITHDRAWAL_REQUESTS);

        $invoices = $withdrawal->invoices()->with('payment')->page($request->query->get('page'));
        return view('components.list.body', [
            'url' => route('admin.withdrawals.withdrawalInvoicesList', ['withdrawal' => $withdrawal->id], false),
            'query' => json_encode($request->all(), JSON_FORCE_OBJECT),
            'header' => ['Amount', 'Issue Date', 'Earned From', 'Paid'],
            'noAction' => true,
            'rows' => $invoices->getCollection()->toString(fn($invoice) => $this->withdrawalInvoicesListRow($invoice)->render()),
            'pagination' => $invoices->links()
        ]);
    }

    private function withdrawalInvoicesListRow(Invoice $invoice) {
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

    ////////////////////////////////////////////////////

    private function withdraw(User $user, Collection $invoices, string $txid, Currency $currency) {
        self::requirePermission('publishers', self::WITHDRAWAL_REQUESTS);

        if (!$user->isPublisher()) {
            abort(404);
        }
        if ($invoices->isEmpty()) {
            abort(500);
        }

        $payment = new Payment;
        $payment->title = 'Archived Withdrawals';
        $payment->user_id = $user->id;
        $payment->amount = round($invoices->sum('amount'), 2);
        $payment->exchange_rate = $currency->exchange_rate ?? 1;
        $payment->currency_id = $currency->id;
        $payment->confirmed_at = Carbon::now()->format('Y-m-d H:i:s');
        $payment->confirmed_by_id = Auth::id();
        $payment->txid = $txid;

        try {
            DB::transaction(function () use ($payment, $invoices) {
                $payment->save();
                $invoices->each(fn($invoice) => $invoice->update([
                    'payment_id' => $payment->id,
                    'title' => DB::raw('CONCAT("Confirmed Withdrawal")'),
                    'archived' => true
                ]));
            });
        } catch (Exception $e) {
            return $this->exception($e);
        }
        return true;
    }

    ////////////////////////////////////////////////////
    private const ANY = 'Any';
    private const ADD_FUND = 'Add Fund';
    private const REMOVE_FUND = 'Remove Fund';
    private const WITHDRAWAL_REQUESTS = 'Withdrawal Requests';

    private static function getPermissionSubject(User $user): string {
        return $user->isAdvertiser() ? 'advertisers' : 'publishers';
    }

    private static function checkPermission(string $subject, string|array $permission): bool {
        $permissions = $permission === self::ANY ? [self::ADD_FUND, self::REMOVE_FUND, self::WITHDRAWAL_REQUESTS] : Arr::wrap($permission);
        return User::hasAnyPermissions($subject, $permissions);
    }

    private static function requirePermission(string $subject, string $permission) {
        if (!self::checkPermission($subject, $permission)) {
            abort(403);
        }
    }

    protected static function isAdmin(): bool {
        return Auth::user()->isAdmin();
    }
}
