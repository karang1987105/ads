<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\Country;
use App\Models\Logs\LoginAttempt;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller {
    /**
     * Display the login view.
     *
     * @return View
     */
    public function create() {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     *
     * @param LoginRequest $request
     * @return RedirectResponse
     */
    public function store(LoginRequest $request) {
        $email = $request->email;

        User::whereEmail($email)
            ->existsOr(function () use (&$request) {
                $request['email'] = null; // Make authentication fails
            });

        try {
            $request->authenticate();

            LoginAttempt::log($email, $request->ip(), true);

        } catch (ValidationException $e) {

            LoginAttempt::log($email, $request->ip(), false);
            session(['isRegister' => '2']);
            return redirect()->back()->withErrors($e->validator)->withInput()->with(['isLogin' => true]);
        }

        $request->session()->regenerate();

        return redirect()->intended(RouteServiceProvider::HOME);
    }

    /**
     * Destroy an authenticated session.
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function destroy(Request $request) {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
