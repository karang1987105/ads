<?php

namespace App\Http\Controllers\Logs;

use App\Http\Controllers\Controller;
use App\Models\Logs\LoginAttempt;
use Illuminate\Http\Request;

class LoginAttemptsController extends Controller {
    public function list(Request $request) {
        $items = LoginAttempt::query()
            ->where('email', auth()->user()->email)
            ->latest()
            ->page($request->query->get('page'));

        return view('components.list.body', [
            'url' => route('logs.logins.list', absolute: false),
            'query' => json_encode($request->all(), JSON_FORCE_OBJECT),
            'header' => ['IP Address', 'Country', 'Time', 'Status'],
            'noAction' => true,
            'pagination' => $items->links(),
            'rows' => $items->getCollection()->toString(function (LoginAttempt $loginAttempt) {
                return view('components.list.row', [
                    'id' => $loginAttempt->id,
                    'columns' => [
                        $loginAttempt->ip,
                        $loginAttempt->country ?? 'N/A',
                        $loginAttempt->created_at->format('Y-m-d H:i'),
                        $loginAttempt->successful ? 
						'<a style="font-family:monospace; color:green; font-weight:bold">SUCCESSFUL!</a>' : 
						'<a style="font-family:monospace; color:red; font-weight:bold">FAILED!</a>'
                    ]
                ])->render();
            })
        ]);
    }
}
