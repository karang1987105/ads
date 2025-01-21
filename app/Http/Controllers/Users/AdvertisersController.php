<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Models\Ad;
use App\Models\Country;
use App\Models\TicketThread;
use App\Models\User;
use App\Models\UserAdvertiser;
use App\Notifications\UserUpdate;
use App\Providers\RouteServiceProvider;
use Arr;
use Auth;
use DB;
use Exception;
use Illuminate\Auth\Events\Registered;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AdvertisersController extends Controller {

    public function __construct() {
        $this->middleware(function ($request, $next) {
            self::requirePermission(self::LIST);
            return $next($request);
        });
    }

    public function index(Request $request) {
        $tables = view('components.list.list', ['key' => 'pending', 'header' => $this->listHeader('pending'), 'body' => $this->list('pending', $request)])
            . view('components.list.list', ['key' => 'all', 'header' => $this->listHeader('all'), 'body' => $this->list('all', $request)])->render();
        return view('layouts.app', ['page_title' => 'Manage Advertisers', 'title' => 'Manage Advertisers', 'slot' => $tables]);
    }

    private function form(UserAdvertiser $advertiser = null) {
        $country_options = Country::visible()->get()->map(fn($country) => ['value' => $country->id, 'caption' => $country->name])->toArray();
        return view('components.advertisers.form', isset($advertiser) ? compact('advertiser', 'country_options') : compact('country_options'));
    }

    private function searchForm(string $key) {
        $country_options = Country::visible()->get()->map(fn($country) => ['value' => $country->id, 'caption' => $country->name])->toArray();
        return view('components.advertisers.search-form', compact('key', 'country_options'));
    }

    public function list($key, Request $request) {
        if ($key == 'all' || $key == 'pending') {
            $builder = $this->getAdvertisers();
            if ($key == 'pending') {
                $builder->inactive();
            }
            if ($this->isSearch()) {
                $builder = $this->search($builder);
            }
            $advertisers = $builder->page($request->query->get('page'));
            $rows = '';
            foreach ($advertisers as $advertiser) {
                $rows .= $this->listRow($advertiser);
            }
            return view('components.list.body', [
                'url' => route('admin.advertisers.list', ['key' => $key], false),
                'query' => json_encode($request->all(), JSON_FORCE_OBJECT),
				'header' => ['Name', 'Email Address', 'Country', 'Last Login', 'Approved', 'Actions'],
				'rows' => $rows,
                'pagination' => $advertisers->links()
            ]);
        } else {
            abort(422);
        }
    }

    private function search(Builder $query): Builder {
        return $query->whereHas('user', function ($q) {
            $req = \request();
            $this->whereString('users.name', $req->name, $q);
            $this->whereString('users.email', $req->email, $q);
            $this->whereString('users.company', $req->company, $q);
            $this->whereString('users.business_id', $req->business_id, $q);
            $this->whereString('users.phone', $req->phone, $q);
            $this->whereString('users.state', $req->state, $q);
            $this->whereString('users.city', $req->city, $q);
            $this->whereString('users.zip', $req->zip, $q);
            $this->whereString('users.address', $req->address, $q);
            $this->whereNotNull('users.active', $req->active, $q);
            $this->whereNotNull('users.email_verified_at', $req->email_verified, $q);
            $this->whereEquals('users.country_id', $req->country_id, $q);
            return $q;
        });
    }

    private function listHeader($key) {
        $params = ['title' => 'Advertisers'];
        if ($key == 'all') {
            $params = ['title' => 'Existing Advertisers', 'search' => $this->searchForm($key)];
            if (self::checkPermission(self::CREATE)) {
                $params['add'] = $this->form();
            }
        } elseif ($key == 'pending') {
            $params = ['title' => 'Pending Advertisers', 'search' => $this->searchForm($key)];
        }
        return view('components.list.header', $params);
    }

    private function listRow(UserAdvertiser $advertiser) {
        $user = $advertiser->user;
        $extra = [];
        if ($user->active) {
            if (self::checkPermission(self::INACTIVATE)) {
                $extra[] = view('components.list.row-action', ['click' => 'Ads.item.updateRow(this)', 'title' => "Suspend",
                    'icon' => "block", 'url' => route('admin.advertisers.activate', ['advertiser' => $user->id, 'active' => 0], false)]);
            }
        } else {
            if (self::checkPermission(self::ACTIVATE)) {
                $extra[] = view('components.list.row-action', ['click' => 'Ads.item.updateRow(this)', 'title' => "Approve",
                    'icon' => "task_alt", 'url' => route('admin.advertisers.activate', ['advertiser' => $user->id, 'active' => 1], false)]);
            }
        }
        if (self::checkPermission(self::LOGIN_BEHALF)) {
            $loginBehalfUrl = route('admin.advertisers.login', ['advertiser' => $user->id], false);
            $extra[] = view('components.list.row-action', ['title' => "Login Behalf", 'icon' => "login", 'url' => '',
                'click' => "Ads.Utils.go('$loginBehalfUrl', false, 'Are you sure to login behalf of \"$user->name\"?')"]);
        }
        if (self::checkPermission(self::SEND_MAIL)) {
            $extra[] = view('components.list.row-action', ['title' => "Send Email", 'icon' => "send", 'url' => '',
                'click' => 'Ads.Utils.go("' . route('admin.emails-templates.index', ['direct' => $user->id], false) . '", false)']);
        }
        if (Auth::user()->isAdmin()) {
            $extra[] = view('components.list.row-action', ['click' => 'Ads.item.openExtra(this)', 'title' => "Domains",
                'icon' => "domain", 'url' => route('admin.advertisers.domains.index', ['advertiser' => $user->id], false)]);
        }
        return view('components.list.row', [
            'id' => $user->id,
            'columns' => [
				$user->name,
				$user->email,
				$user->country?->name,
                $user->lastLoginAttempts()->where('successful', true)->value('created_at')?->format('Y-m-d H:i') ?? 'Never',
				$user->active ? '<a class="approved">✔</a>' : '<a class="declined">✘</a>'
            ],
            'show' => ['url' => route('admin.advertisers.show', ['advertiser' => $user->id], false)],
            'edit' => self::checkPermission(self::UPDATE) ? ['url' => route('admin.advertisers.edit', ['advertiser' => $user->id], false)] : null,
            'delete' => self::checkPermission(self::DELETE) ? ['url' => route('admin.advertisers.destroy', ['advertiser' => $user->id], false)] : null,
            'extra' => $extra
        ]);
    }

    public function show(UserAdvertiser $advertiser) {
        $user = $advertiser->user;
        return view('components.list.row-details', [
            'rows' => [
                ['caption' => 'Advertiser ID:', 'value' => $user->id],
                ['caption' => 'Name:', 'value' => $user->name],
                ['caption' => 'Email Address:', 'value' => $user->email],
                ['caption' => 'Registration Date:', 'value' => $user->created_at->format('Y-m-d H:i')],     
                ['caption' => 'Approved:', 'value' => isset($user->active) ? '<a class="approved">✔</a>' : '<a class="declined">✘</a>'],
                ['caption' => 'Email Verified:', 'value' => isset($user->email_verified_at) ? '<a class="approved">✔</a>' : '<a class="declined">✘</a>'],
                ['caption' => 'Last Login:', 'value' => $user->lastLoginAttempts()->where('successful', true)->value('created_at')?->format('Y-m-d H:i') ?? 'Never'],
                ['caption' => 'Login Location:', 'value' => $user->lastLoginAttempts()->where('successful', true)?->value('country') ?? 'None'],
                ['caption' => 'IP Address:', 'value' => $user->lastLoginAttempts()->where('successful', true)?->value('ip') ?? 'None'],                           
                ['caption' => 'Approved By:', 'full' => true, 'value' =>
				(isset($user->active) && isset($user->active_by_id)) ? "{$user->activeBy->user->name} at {$user->active->format('Y-m-d H:i')}" : '' .
				(isset($user->active) ? "{$user->name} at {$user->active->format('Y-m-d H:i')}" : 'Nobody')],
				['caption' => 'Company:', 'value' => $user->company],
                ['caption' => 'Phone:', 'value' => $user->phone],
                ['caption' => 'Business ID:', 'value' => $user->business_id],
                ['caption' => 'Country:', 'value' => $user->country?->name],
                ['caption' => 'State:', 'value' => $user->state],
                ['caption' => 'City:', 'value' => $user->city],
                ['caption' => 'Zipcode:', 'value' => $user->zip],
                ['caption' => 'Address:', 'full' => true, 'value' => $user->address],
            ]
        ]);
    }

    public function activate(UserAdvertiser $advertiser, $active) {
        self::requirePermission($active ? self::ACTIVATE : self::INACTIVATE);

        $user = $advertiser->user;
        if ($active && !isset($user->active)) {
            $user->active = now();
            $user->active_by_id = auth()->id();
        } else {
            if (!$active && isset($user->active)) {
                $user->active = null;
                $user->active_by_id = auth()->id();
            }
        }
        $user->save();

        $user->notifyUser($user->active ? UserUpdate::$TYPE_ACCOUNT_ACTIVATED : UserUpdate::$TYPE_ACCOUNT_SUSPENDED);

        return $this->listRow($advertiser->fresh());
    }

    public function loginBehalf(UserAdvertiser $advertiser) {
        self::requirePermission(self::LOGIN_BEHALF);

        Auth::login($advertiser->user);
        return redirect(RouteServiceProvider::HOME);
    }

    public function edit(UserAdvertiser $advertiser) {
        self::requirePermission(self::UPDATE);

        return $this->form($advertiser);
    }

    public function domains(UserAdvertiser $advertiser, Request $request) {
        return (new AdvertisersDomainsController)->index($advertiser, $request);
    }

    public function destroy(UserAdvertiser $advertiser) {
        self::requirePermission(self::DELETE);
        return DB::transaction(function () use ($advertiser) {
            TicketThread::deleteUserThreads($advertiser->user_id);

            Ad::withTrashed()->where('advertiser_id', $advertiser->user_id)->forceDelete();

            return $advertiser->user->delete();
        });
    }

    public function store(Request $request) {
        self::requirePermission(self::CREATE);

        $fields = [
            'name' => 'required|regex:/^[A-Z0-9\s]+$/i|max:255',
            'email' => 'required|string|email:filter|max:255|unique:users',
            'password' => 'required|string|confirmed|min:8',
        ];

        $requiredFields = Arr::wrap(config('ads.advertisers.required_fields'));
        foreach ($requiredFields as $requiredField => $required) {
            if ($required) {
                $fields[$requiredField] = 'required';
            }
        }

        $validator = Validator::make($request->all(), $fields);
        if ($validator->fails()) {
            return $this->failure($validator->errors());
        }

        if (isset($request->country_id) && !Country::where('id', '=', $request->country_id)->exists()) {
            return $this->failure(['country_id' => 'Country is not valid.']);
        }

        $fields = [
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'type' => 'Advertiser',
            'active' => isset($request->active) ? now() : null,
            'email_verified_at' => isset($request->email_verified) ? now() : null,
            'country_id' => $request->country_id,
            'company' => $request->company ?? '',
            'phone' => $request->phone ?? '',
            'business_id' => $request->business_id ?? '',
            'address' => $request->address ?? '',
            'state' => $request->state ?? '',
            'city' => $request->city ?? '',
            'zip' => $request->zip ?? '',
            'notifications' => !empty($request->notifications) ? array_keys($request->notifications) : []
        ];
        try {
            $user = DB::transaction(fn() => User::create($fields)->advertiser()->save(new UserAdvertiser()));
            event(new Registered($user));
            return $this->success($this->listRow($user)->render());

        } catch (Exception $e) {
            return $this->exception($e);
        }
    }

    public function update(Request $request, UserAdvertiser $advertiser) {
        self::requirePermission(self::UPDATE);

        $fields = ['name' => 'required|regex:/^[A-Z0-9\s]+$/i|max:255'];
        if ($request->email !== $advertiser->user->email) {
            $fields['email'] = 'required|string|email:filter|max:255|unique:users';
        }

        if (isset($request->password)) {
            $fields['password'] = 'required|string|confirmed|min:8';
        } else {
            $request->offsetUnset('password');
        }

        $requiredFields = Arr::wrap(config('ads.advertisers.required_fields'));
        foreach ($requiredFields as $requiredField => $required) {
            if ($required) {
                $fields[$requiredField] = 'required';
            }
        }

        $validator = Validator::make($request->all(), $fields);
        if ($validator->fails()) {
            return $this->failure($validator->errors());
        }

        if (isset($request->country_id) && !Country::where('id', '=', $request->country_id)->exists()) {
            return $this->failure(['country_id' => 'Country is not valid.']);
        }

        if (isset($request->password)) {
            $request['password'] = Hash::make($request->password);
        }

        $request['company'] = $request->company ?? '';
        $request['phone'] = $request->phone ?? '';
        $request['business_id'] = $request->business_id ?? '';
        $request['address'] = $request->address ?? '';
        $request['state'] = $request->state ?? '';
        $request['city'] = $request->city ?? '';
        $request['zip'] = $request->zip ?? '';
        $request['notifications'] = !empty($request->notifications) ? array_keys($request->notifications) : [];
        $request->offsetUnset('type');
        $request['active'] = isset($request->active) ? now() : null;

        $accountSuspended = $advertiser->user->active !== null && $request['active'] === null;
        $accountActivated = $advertiser->user->active === null && $request['active'] !== null;

        try {
            if (isset($request->email_verified)) {
                if (!$advertiser->user->hasVerifiedEmail()) {
                    $request['email_verified_at'] = now();
                } else {
                    $request->except('email_verified_at');
                }
            } elseif ($advertiser->user->hasVerifiedEmail()) {
                $request['email_verified_at'] = null;
            }
            $advertiser->user->update($request->all());

            if ($accountSuspended) {
                $advertiser->user->notifyUser(UserUpdate::$TYPE_ACCOUNT_SUSPENDED);
            } elseif ($accountActivated) {
                $advertiser->user->notifyUser(UserUpdate::$TYPE_ACCOUNT_ACTIVATED);
            }

            return $this->success($this->listRow($advertiser->fresh())->render());
        } catch (Exception $e) {
            return $this->exception($e);
        }
    }

    private function getAdvertisers(): Builder {
        return UserAdvertiser::with('user.country:id,name');
    }

    ////////////////////////////////////////////////////
    private const LIST = 'List';
    private const CREATE = 'Create';
    private const UPDATE = 'Update';
    private const DELETE = 'Delete';
    private const ACTIVATE = 'Activate';
    private const INACTIVATE = 'Block';
    private const LOGIN_BEHALF = 'Login Behalf';
    private const SEND_MAIL = 'Send Email';

    private static function checkPermission(string $permission): bool {
        return User::hasAllPermissions('advertisers', $permission === self::LIST ? [self::LIST] : [self::LIST, $permission]);
    }

    private static function requirePermission(string $permission) {
        if (!self::checkPermission($permission)) {
            abort(403);
        }
    }
}
