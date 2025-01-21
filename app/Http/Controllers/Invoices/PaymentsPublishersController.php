<?php

namespace App\Http\Controllers\Invoices;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

// Archived Payments
class PaymentsPublishersController extends Controller {

    public function index(Request $request) {
        return view('components.list.list', [
            'key' => 'archived',
            'header' => $this->listHeader(),
            'body' => $this->list($request)
        ])->render();
    }

    public function list(Request $request) {
        $publisher = auth()->user()->publisher;
        $payments = $this->search(Payment::where('user_id', $publisher->user_id))
            ->orderByDesc('created_at')
            ->page($request->query->get('page'));

        return view('components.list.body', [
            'url' => route('publisher.payments.list', absolute: false),
            'query' => json_encode($request->all(), JSON_FORCE_OBJECT),
            'header' => ['Amount', 'Crypto Amount', 'Payment Date', 'Paid', 'Actions'],
            'rows' => $payments->getCollection()->toString(fn($payment) => $this->listRow($payment)->render()),
            'pagination' => $payments->links()
        ]);
    }

    private function search(Builder $q): Builder {
        if ($this->isSearch()) {
            $req = \request();
            $this->whereGt('payments.amount', $req->amount_gt, $q);
            $this->whereLt('payments.amount', $req->amount_lt, $q);
            $this->whereGt('payments.created_at', $req->issue_after, $q);
            $this->whereLt('payments.created_at', $req->issue_before, $q);
        }
        return $q;
    }

    private function listRow(Payment $payment) {
    $decimals = config('ads.crypto_decimal_places_length');
            return view('components.list.row', [
                'id' => $payment->id,
                'columns' => [
                    Helper::amount($payment->amount, 2),
                    sprintf("%.{$decimals}f", round($payment->amount * $payment->exchange_rate, $decimals)) . ' ' . $payment->currency_id,
                    $payment->created_at->format('Y-m-d H:i'),
                    $payment->isWithdrawalRequest() ? '<a class="approved">✔</a>' : '<a class="declined">✘</a>',
                ],
                'show' => ['url' => route('publisher.payments.show', ['payment' => $payment->id], false)]
            ]);
        }

    private function listHeader() {
        return view('components.list.header', [
            'title' => 'Archived Payments',
            'search' => view('components.invoices.publisher-search-form', ['key' => 'archived'])
        ]);
    }

    public function show(Payment $payment) {
        $rows = [
            ['caption' => 'Withdrawal Payment ID:', 'value' => $payment->id],
            ['caption' => 'Amount:', 'value' => Helper::amount($payment->amount, 2)],
            ['caption' => 'Archived:', 'value' => $payment->isArchived() ? '<a class="approved">✔</a>' : 'No'],
        ];
  
			if ($payment->isConfirmed()) {
            $rows[] = [
                'caption' => 'Payment Date:',
                'value' => isset($payment->confirmed_at) ? ' ' . $payment->confirmed_at->format('Y-m-d H:i') : 'Unconfirmed',
		   ];

            if ($payment->amount > 0) {
            $decimals = config('ads.crypto_decimal_places_length');
            $rows[] = [
                'caption' => 'Crypto Amount:',
                'value' => sprintf("%.{$decimals}f", round($payment->amount * $payment->exchange_rate, $decimals)) . ' ' . $payment->currency_id,
				];
			$rows[] = [
				'caption' => 'Transaction ID:', 'value' => $payment->txid,
				'full' => 'true'
			];

            }
        } else {
            $rows[] = ['caption' => 'Paid', 'value' => 'No'];
            $rows[] = [
                'caption' => 'Withdrawal requested',
                'value' => $payment->isWithdrawalRequest() ? 'Yes' : 'No'
            ];
        }
        return view('components.list.row-details', ['rows' => $rows]);
    }
}