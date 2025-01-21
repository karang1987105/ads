<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\TicketThread;
use App\Models\User;
use App\Models\UserPublisher;
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

class PublishersController extends Controller {

    public function __construct() {
        $this->middleware(function ($request, $next) {
            self::requirePermission(self::LIST);
            return $next($request);
        });
    }

    public function index(Request $request) {
        $tables = view('components.list.list', ['key' => 'pending', 'header' => $this->listHeader('pending'), 'body' => $this->list('pending', $request)])
            . view('components.list.list', ['key' => 'all', 'header' => $this->listHeader('all'), 'body' => $this->list('all', $request)])->render();
        return view('layouts.app', ['page_title' => 'Manage Publishers', 'title' => 'Manage Publishers', 'slot' => $tables]);
    }

    private function form(UserPublisher $publisher = null) {
        $country_options = Country::visible()->get()->map(fn($country) => ['value' => $country->id, 'caption' => $country->name])->toArray();
        return view('components.publishers.form', isset($publisher) ? compact('publisher', 'country_options') : compact('country_options'));
    }

    private function searchForm(string $key) {
        $country_options = Country::visible()->get()->map(fn($country) => ['value' => $country->id, 'caption' => $country->name])->toArray();
        return view('components.publishers.search-form', compact('key', 'country_options'));
    }

    public function list($key, Request $request) {
        if ($key == 'all' || $key == 'pending') {
            $builder = $this->getPublishers();
            if ($key == 'pending') {
                $builder->inactive();
            }
            if ($this->isSearch()) {
                $builder = $this->search($builder);
            }
            $publishers = $builder->page($request->query->get('page'));
            $rows = $publishers->getCollection()->map(fn($publisher) => $this->listRow($publisher)->render())->join('');
            return view('components.list.body', [
                'url' => route('admin.publishers.list', ['key' => $key], false),
                'query' => json_encode($request->all(), JSON_FORCE_OBJECT),
                'header' => ['Name', 'Email Address', 'Country', 'Last Login', 'Approved', 'Actions'],
                'rows' => $rows,
                'pagination' => $publishers->links()
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
        $params = ['title' => 'Publishers'];
        if ($key == 'all') {
            $params = ['title' => 'Existing Publishers', 'search' => $this->searchForm($key)];
            if (self::checkPermission(self::CREATE)) {
                $params['add'] = $this->form();
            }
        } elseif ($key == 'pending') {
            $params = ['title' => 'Pending Publishers', 'search' => $this->searchForm($key)];
        }
        return view('components.list.header', $params);
    }

	private function listRow(UserPublisher $publisher) {
        $user = $publisher->user;
        $extra = [];
		if ($user->active) {
        if (self::checkPermission(self::INACTIVATE)) {
            $extra[] = view('components.list.row-action', ['click' => 'Ads.item.updateRow(this)', 'title' => "Suspend",
            'icon' => "block", 'url' => route('admin.publishers.activate', ['publisher' => $user->id, 'active' => 0], false)]);
            }
        } else {
        if (self::checkPermission(self::ACTIVATE)) {
            $extra[] = view('components.list.row-action', ['click' => 'Ads.item.updateRow(this)', 'title' => "Approve",
            'icon' => "task_alt", 'url' => route('admin.publishers.activate', ['publisher' => $user->id, 'active' => 1], false)]);
			}
		}
        if (self::checkPermission(self::LOGIN_BEHALF)) {
            $loginBehalfUrl = route('admin.publishers.login', ['publisher' => $user->id], false);
            $extra[] = view('components.list.row-action', ['title' => "Login Behalf", 'icon' => "login", 'url' => '',
                'click' => "Ads.Utils.go('$loginBehalfUrl', false, 'Are you sure to login behalf of \"$user->name\"?')"]);
        }
        if (self::checkPermission(self::SEND_MAIL)) {
            $extra[] = view('components.list.row-action', ['title' => "Send Email", 'icon' => "send", 'url' => '',
                'click' => 'Ads.Utils.go("' . route('admin.emails-templates.index', ['direct' => $user->id], false) . '", false)']);
        }
        if (Auth::user()->isAdmin()) {
            $extra[] = view('components.list.row-action', ['click' => 'Ads.item.openExtra(this)', 'title' => "Domains",
                'icon' => "domain", 'url' => route('admin.publishers.domains.index', ['publisher' => $user->id], false)]);
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
            'show' => ['url' => route('admin.publishers.show', ['publisher' => $user->id], false)],
            'edit' => self::checkPermission(self::UPDATE) ? ['url' => route('admin.publishers.edit', ['publisher' => $user->id], false)] : null,
            'delete' => self::checkPermission(self::DELETE) ? ['url' => route('admin.publishers.destroy', ['publisher' => $user->id], false)] : null,
            'extra' => $extra
        ]);
    }

    public function show(UserPublisher $publisher) {
        $user = $publisher->user;
        return view('components.list.row-details', [
            'rows' => [
                ['caption' => 'Publisher ID:', 'value' => $user->id],
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

    public function activate(UserPublisher $publisher, $active) {
        self::requirePermission($active ? self::ACTIVATE : self::INACTIVATE);

        $user = $publisher->user;
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

        return $this->listRow($publisher->fresh());
    }

    public function loginBehalf(UserPublisher $publisher) {
        self::requirePermission(self::LOGIN_BEHALF);

        Auth::login($publisher->user);
        return redirect(RouteServiceProvider::HOME);
    }

    public function edit(UserPublisher $publisher) {
        self::requirePermission(self::UPDATE);

        return $this->form($publisher);
    }

    public function domains(UserPublisher $publisher, Request $request) {
        return (new PublishersDomainsController)->index($publisher, $request);
    }

    public function destroy(UserPublisher $publisher) {
        self::requirePermission(self::DELETE);
        return DB::transaction(function () use ($publisher) {
            TicketThread::deleteUserThreads($publisher->user_id);
            return $publisher->user->delete();
        });
    }

    public function store(Request $request) {
        self::requirePermission(self::CREATE);

        $fields = [
            'name' => 'required|regex:/^[A-Z0-9\s]+$/i|max:255',
            'email' => 'required|string|email:filter|max:255|unique:users',
            'password' => 'required|string|confirmed|min:8',
        ];

        $requiredFields = Arr::wrap(config('ads.publishers.required_fields'));
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
            'type' => 'Publisher',
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
            $user = DB::transaction(fn() => User::create($fields)->publisher()->save(new UserPublisher()));
            event(new Registered($user));
            return $this->success($this->listRow($user)->render());

        } catch (Exception $e) {
            return $this->exception($e);
        }
    }

    public function update(Request $request, UserPublisher $publisher) {
        self::requirePermission(self::UPDATE);

        $fields = ['name' => 'required|regex:/^[A-Z0-9\s]+$/i|max:255'];
        if ($request->email !== $publisher->user->email) {
            $fields['email'] = 'required|string|email:filter|max:255|unique:users';
        }

        if (isset($request->password)) {
            $fields['password'] = 'required|string|confirmed|min:8';
        } else {
            $request->offsetUnset('password');
        }

        $requiredFields = Arr::wrap(config('ads.publishers.required_fields'));
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

        $accountSuspended = $publisher->user->active !== null && $request['active'] === null;
        $accountActivated = $publisher->user->active === null && $request['active'] !== null;

        try {
            if (isset($request->email_verified)) {
                if (!$publisher->user->hasVerifiedEmail()) {
                    $request['email_verified_at'] = now();
                } else {
                    $request->except('email_verified_at');
                }
            } elseif ($publisher->user->hasVerifiedEmail()) {
                $request['email_verified_at'] = null;
            }
            $publisher->user->update($request->all());

            if ($accountSuspended) {
                $publisher->user->notifyUser(UserUpdate::$TYPE_ACCOUNT_SUSPENDED);
            } elseif ($accountActivated) {
                $publisher->user->notifyUser(UserUpdate::$TYPE_ACCOUNT_ACTIVATED);
            }

            return $this->success($this->listRow($publisher->fresh())->render());
        } catch (Exception $e) {
            return $this->exception($e);
        }
    }

    private function getPublishers(): Builder {
        return UserPublisher::with('user.country:id,name');
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
        return User::hasAllPermissions('publishers', $permission === self::LIST ? [self::LIST] : [self::LIST, $permission]);
    }

    private static function requirePermission(string $permission) {
        if (!self::checkPermission($permission)) {
            abort(403);
        }
    }
}
