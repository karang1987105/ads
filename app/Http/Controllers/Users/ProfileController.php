<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Models\Country;
use Auth;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Str;

class ProfileController extends Controller {
    public function index() {
        return view('layouts.app', [
            'page_title' => 'Update Profile',
            'title' => 'Update Profile',
            'slot' => $this->list()
        ]);
    }

    public function update(Request $request) {
        $user = Auth::user();
        $rules = [
            'name' => 'regex:/^[A-Z0-9\s]+$/i|max:255'
        ];

        if ($user->isAdmin()) {
            $rules['email'] = [
                'required',
                'string',
                'email:filter',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ];
        }

        if (isset($request->password)) {
            $rules['password'] = 'required|string|confirmed|min:8';
        }

        $type = Str::plural(strtolower($user->type));
        $requiredFields = \Arr::wrap(config('ads.' . $type . '.required_fields'));
        foreach ($requiredFields as $requiredField => $required) {
            if ($required) {
                $rules[$requiredField] = 'required';
            }
        }

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return $this->failure($validator->errors());
        }

        if (isset($request->country_id) && !Country::where('id', '=', $request->country_id)->exists()) {
            return $this->failure(['country_id' => 'Country is not valid.']);
        }

        $attributes = [
            'name' => $request->name,
            'company' => $request->company ?? '',
            'business_id' => $request->business_id ?? '',
            'phone' => $request->phone ?? '',
            'country_id' => $request->country_id,
            'state' => $request->state ?? '',
            'city' => $request->city ?? '',
            'zip' => $request->zip ?? '',
            'address' => $request->address ?? '',
            'notifications' => !empty($request->notifications) ? array_keys($request->notifications) : []
        ];

        if (isset($request->password)) {
            $attributes['password'] = Hash::make($request->password);
        }

        if ($user->isAdmin()) {
            $attributes['email'] = $request->email;
        }

        try {
            $user->update($attributes);
            return $this->success([
                'list' => $this->list()->render(),
                'alert' => view('components.page-message', [
                    'class' => 'alert-success',
                    'icon' => 'fa-check',
                    'message' => '<a style="font-weight:bold">Success! Your profile has been updated!</a>'
                ])->render()
            ]);
        } catch (Exception $e) {
            return $this->exception($e);
        }
    }

    private function list() {
        $country_options = Country::visible()->get()->map(fn($country) => ['value' => $country->id, 'caption' => $country->name])->toArray();
        return view('components.list.list', [
            'key' => 'all',
            'nobody' => true,
            'header' => view('components.list.header', [
                'title' => 'Profile Details',
                'refresh' => false,
                'slot' => view('components.profile', ['user' => Auth::user(), 'country_options' => $country_options])
            ])
        ]);
    }
}
