<?php

namespace App\Http\Controllers\Logs;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LogsController extends Controller {
    public function index(Request $request) {
        return view('layouts.app', [
            'page_title' => 'Login Logs',
            'slot' => view('components.list.list', [
                'key' => 'logins',
                'header' => view('components.list.header', ['title' => 'Login Attempts']),
                'body' => (new LoginAttemptsController())->list($request)
            ])
        ]);
    }
}
