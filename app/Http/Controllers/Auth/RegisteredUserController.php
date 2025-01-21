<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\User;
use App\Models\UserAdvertiser;
use App\Models\UserPublisher;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Str;

class RegisteredUserController extends Controller {
    /**
     * Display the registration view.
     *
     * @return View
     */
    public function create() {
        $countries = Country::visible()->get()->map(fn($country) => ['value' => $country->id, 'caption' => $country->name])->toArray();
        return view('auth.register', ['country_options' => $countries]);
    }

    /**
     * Handle an incoming registration request.
     *
     * @param Request $request
     * @return RedirectResponse
     *
     * @throws ValidationException
     */
    public function store(Request $request) {
        $rules = [
            'name' => 'required|regex:/^[A-Z0-9\s]+$/i|max:255',
            'email' => 'required|string|email:filter|max:255|unique:users',
            'password' => ['required', 'string', 'confirmed', Rules\Password::min(8)->mixedCase()->numbers()->symbols()],
            'type' => 'in:Advertiser,Publisher',

            'tos' => 'required'
        ];

        $type = Str::lower(Str::plural($request->type));
        $requiredFields = \Arr::wrap(config('ads.' . $type . '.required_fields'));
        foreach ($requiredFields as $requiredField => $required) {
            if ($required) {
                $rules[$requiredField] = 'required';
            }
        }

        try {
            $request->validate(
                $rules,
                ['tos.required' => 'You need to agree to our Terms Of Service!']
            );
        } catch (ValidationException $e) {


            session(['isRegister' => '1']);
            return redirect()->back()->withErrors($e->validator)->withInput();
        }

        $notifications = ['Account', 'Domain'];
        if ($request->type === 'Advertiser') {
            $notifications[] = 'Advertisement';
            $notifications[] = 'Campaign';

        } elseif ($request->type === 'Publisher') {
            $notifications[] = 'Place';
        }

        /** @var User $user */
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'type' => $request->type,
            'country_id' => $request->country_id,
            'company' => $request->company ?? '',
            'phone' => $request->phone ?? '',
            'business_id' => $request->business_id ?? '',
            'address' => $request->address ?? '',
            'state' => $request->state ?? '',
            'city' => $request->city ?? '',
            'zip' => $request->zip ?? '',
            'active' => now(),
            'notifications' => $notifications
        ]);

        if ($user->isAdvertiser()) {
            $user->advertiser()->save(new UserAdvertiser());
        } else {
            $user->publisher()->save(new UserPublisher());
        }

        event(new Registered($user));

        Auth::login($user);

        return redirect(RouteServiceProvider::HOME);
    }
}
