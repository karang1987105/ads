<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller {
    /**
     * Display the password reset link request view.
     *
     * @return View
     */
    public function create() {
        return view('auth.forgot-password', ['page_title' => 'Password Reset']);
    }

    /**
     * Handle an incoming password reset link request.
     *
     * @param Request $request
     * @return RedirectResponse
     *
     * @throws ValidationException
     */
    public function store(Request $request) {
        try {
            $request->validate([
                'email' => 'required|email',
            ]);
        }
        catch (ValidationException $e) {
            session(['isRegister' => '3']);
            return redirect()->back()->withErrors($e->validator)->withInput();
        }

        // We will send the password reset link to this user. Once we have attempted
        // to send the link, we will examine the response then see the message we
        // need to show to the user. Finally, we'll send out a proper response.
        $status = Password::sendResetLink(
            $request->only('email')
        );
        
        if ($status == Password::RESET_LINK_SENT) {
            return back()->with('status', __($status));
        }

        else {
                session(['isRegister' => '3']);
                return back()->withInput($request->only('email'))
                            ->withErrors(['email' => __($status)]);
            }
        // return $status == Password::RESET_LINK_SENT
        //     ? back()->with('status', __($status))
        //     : back()->withInput($request->only('email'))
        //         ->withErrors(['email' => __($status)]);
    }
}
