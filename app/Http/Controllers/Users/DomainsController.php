<?php

namespace App\Http\Controllers\Users;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Domains\AdvertiserDomain;
use App\Models\Domains\PublisherDomain;
use App\Models\User;
use App\Rules\Domain;
use App\Rules\UniqueDomain;
use Auth;
use DB;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Validator;

class DomainsController extends Controller {
    private null|string $userType = null;

    public function __construct() {
        $this->middleware(function ($request, $next) {
            $this->requirePermission();

            return $next($request);
        });
    }

    ////////////////////////////////////////////////////////////////////////////////////////
    // Publishers or Advertisers actions
    ////////////////////////////////////////////////////////////////////////////////////////
    private function isPublisher(): bool {
        if ($this->userType === null) {
            $this->userType = Auth::user()->type;
        }
        return $this->userType === 'Publisher';
    }

    private function isAdvertiser(): bool {
        if ($this->userType === null) {
            $this->userType = Auth::user()->type;
        }
        return $this->userType === 'Advertiser';
    }

    private function route($route, array $data = []) {
        return route(($this->isPublisher() ? 'publisher' : 'advertiser') . '.' . $route, $data, false);
    }

    public function index(Request $request) {
        return view('layouts.app', [
            'page_title' => 'Manage Domains',
            'slot' => view('components.list.list', [
                'key' => 'all',
                'header' => $this->listHeader(),
                'body' => $this->list($request)
            ])
        ]);
    }

    public function list(Request $request) {
        $user = Auth::user();
        $domains = null;
        if ($this->isPublisher()) {
            $header = ['Domain', 'Category', 'Approved', 'Actions'];
            $domains = $user->publisher->domains()
                ->with('category')
                ->addSelect(['active_places' => PublisherDomain::activePlacesCount()]);

        } elseif ($this->isAdvertiser()) {
            $header = ['Domain', 'Approved', 'Actions'];
            $domains = $user->advertiser->domains()
                ->select(['users_advertisers_domains.*', DB::raw('active_ads.cnt AS active_ads')])
                ->leftJoinSub(AdvertiserDomain::activeAdsJoin($user->advertiser->user_id), 'active_ads', 'active_ads.id', '=', 'users_advertisers_domains.id');
        }
        $rows = '';
        if ($domains != null) {
            $domains = $domains->page($request->query->get('page'));
            $rows = $domains->getCollection()->map(fn($domain) => $this->listRow($domain)->render())->join('');
        }
        return view('components.list.body', [
            'url' => $this->route('domains.list'),
            'query' => json_encode($request->all(), JSON_FORCE_OBJECT),
            'header' => $header,
            'rows' => $rows,
            'pagination' => $domains->links()
        ]);
    }

    protected function listRow($domain) {
        $edit = $delete = null;
        $extra = [];
        $inUse = $this->isPublisher() ? (isset($domain->active_places) ? $domain->active_places > 0 : PublisherDomain::inUse($domain))
            : (isset($domain->active_ads) ? $domain->active_ads > 0 : AdvertiserDomain::inUse($domain));
        if (!$inUse) {
            $edit = ['url' => $this->route('domains.edit', ['domain' => $domain->id])];
            $delete = ['url' => $this->route('domains.destroy', ['domain' => $domain->id])];
        } else {
            $extra[] = view('components.list.row-action', ['class' => 'disabled', 'title' => 'This domain is already in use', 'icon' => 'delete_forever'])->render();
            $extra[] = view('components.list.row-action', ['class' => 'disabled', 'title' => 'This domain is already in use', 'icon' => 'border_color'])->render();
        }
        if ($this->isPublisher()) {
            $columns = [$domain->domain, $domain->category?->title, $domain->isApproved() ? '<a class="approved">✔</a>' : '<a class="declined">✘</a>'];
        } else {
            $columns = [$domain->domain, $domain->isApproved() ? '<a class="approved">✔</a>' : '<a class="declined">✘</a>'];
        }
        return view('components.list.row', [
            'id' => $domain->id,
            'columns' => $columns,
            'show' => ['url' => $this->route('domains.show', ['domain' => $domain->id])],
            'edit' => $edit,
            'delete' => $delete,
            'extra' => $extra
        ]);
    }

    private function listHeader() {
        return view('components.list.header', ['title' => 'Available Domains', 'add' => $this->form()]);
    }

    private function form($domain = null) {
        $data = ['route' => $this->isPublisher() ? 'publisher' : 'advertiser'];
        if ($domain != null) {
            $data['domain'] = $domain;
        }
        return view('components.domains.form', $data);
    }

    public function show($domainId) {
        $domain = null;
        if ($this->isAdvertiser()) {
            $domain = AdvertiserDomain::find($domainId);
        } elseif ($this->isPublisher()) {
            $domain = PublisherDomain::find($domainId);
        }

        if ($domain != null) {
            $rows = [
                ['caption' => 'Domain ID:', 'value' => $domain->id],
                ['caption' => 'Domain:', 'value' => $domain->domain],
                ['caption' => 'Approved:', 'value' => $domain->isApproved() ? '<a class="approved">✔</a>' : '<a class="declined">✘</a>'],
            ];
            if ($this->isPublisher()) {
                $rows[] = ['caption' => 'Category:', 'value' => $domain->category?->title];
            }
            return view('components.list.row-details', ['rows' => $rows]);
        }

        abort(404);
    }

    public function edit($domainId) {
        $domain = null;
        if ($this->isAdvertiser()) {
            $domain = AdvertiserDomain::find($domainId);
        } elseif ($this->isPublisher()) {
            $domain = PublisherDomain::find($domainId);
        }
        if ($domain != null) {
            return $this->form($domain);
        }
        abort(404);
    }

    public function destroy($domainId) {
        $domain = $this->isAdvertiser() ? AdvertiserDomain::find($domainId) : PublisherDomain::find($domainId);
        if ($domain != null) {
            if ($domain->isApproved()) {
                if ($this->isAdvertiser() ? AdvertiserDomain::inUse($domain) : PublisherDomain::inUse($domain)) {
                    abort(422);
                }
            }
            return $domain->delete();
        } else {
            abort(404);
        }
    }

    public function store(Request $request) {
        $request['domain'] = Domain::sanitizeDomain($request->domain);

        $domainRules = ['required', new Domain()];
        if ($this->isPublisher()) {
            $domainRules[] = new UniqueDomain('users_publishers_domains', 'domain');
        } elseif ($this->isAdvertiser()) {
            $domainRules[] = new UniqueDomain('users_advertisers_domains', 'domain');
        }

        $validator = Validator::make($request->all(), [
            'domain' => $domainRules
        ]);
        if ($validator->fails()) {
            return $this->failure($validator->errors());
        }

        try {
            $user = Auth::user();

            $domain = null;
            if ($this->isAdvertiser()) {
                $domain = AdvertiserDomain::create(['advertiser_id' => $user->id, 'domain' => $request->domain]);
            } elseif ($this->isPublisher()) {
                $domain = PublisherDomain::create(['publisher_id' => $user->id, 'domain' => $request->domain]);
            }
            if ($domain != null) {
                return $this->success($this->listRow($domain)->render());
            }

        } catch (QueryException $e) {
            if ($e->getCode() == 23000) {
                return $this->failure(['domain' => 'This domain exists already!']);
            }
            return $this->exception($e);
        } catch (Exception $e) {
            return $this->exception($e);
        }
    }

    public function update($domainId, Request $request) {
        $request['domain'] = Domain::sanitizeDomain($request->domain);

        $domain = null;
        if ($this->isAdvertiser()) {
            $domain = AdvertiserDomain::find($domainId);
            if (AdvertiserDomain::inUse($domain)) {
                abort(422);
            }
        } elseif ($this->isPublisher()) {
            $domain = PublisherDomain::find($domainId);
            if (PublisherDomain::inUse($domain)) {
                abort(422);
            }
        }
        if ($domain != null) {
            $domainRules = ['required', new Domain()];
            if ($this->isPublisher()) {
                $domainRules[] = new UniqueDomain('users_publishers_domains', 'domain', $domainId);
            } elseif ($this->isAdvertiser()) {
                $domainRules[] = new UniqueDomain('users_advertisers_domains', 'domain', $domainId);
            }

            $validator = Validator::make($request->all(), [
                'domain' => $domainRules
            ]);
            if ($validator->fails()) {
                return $this->failure($validator->errors());
            }

            $request['approved_by_id'] = null;
            $request['approved_at'] = null;

            try {
                $domain->update($request->all());
                return $this->success($this->listRow($domain->fresh())->render());
            } catch (QueryException $e) {
                if ($e->getCode() == 23000) {
                    return $this->failure(['domain' => 'This domain exists already!']);
                }
                return $this->exception($e);
            } catch (Exception $e) {
                return $this->exception($e);
            }
        }
        abort(404);
    }

    ////////////////////////////////////////////////////////////////////////////////////////
    // Admin and Manager actions
    ////////////////////////////////////////////////////////////////////////////////////////
    public function indexUsers(Request $request) {
        $sections = '';

        if (auth()->user()->isAdmin()) {
            $sections .= (new AdminsDomainsController)->index();
        }

        if ($this->isPublishersManager()) {
            $sections .= view('components.list.list', [
                'key' => 'publishers',
                'header' => view('components.list.header', [
                    'title' => 'Publisher Domains',
                    'search' => $this->usersSearchForm('publishers'),
                    'actions' => []
                ]),
                'body' => $this->listUsers('publishers', $request)
            ])->render();
        }

        if ($this->isAdvertisersManager()) {
            $sections .= view('components.list.list', [
                'key' => 'advertisers',
                'header' => view('components.list.header', [
                    'title' => 'Advertiser Domains',
                    'search' => $this->usersSearchForm('advertisers'),
                    'actions' => []
                ]),
                'body' => $this->listUsers('advertisers', $request)
            ])->render();
        }

        return view('layouts.app', [
            'page_title' => 'Manage Domains',
            'slot' => $sections
        ]);
    }

    public function listUsers($key, Request $request) {
        if ($key === 'advertisers' && !$this->isAdvertisersManager() || $key === 'publishers' && !$this->isPublishersManager()) {
            abort(422);
        }

        if ($key === 'advertisers') {
            $query = User::asAdvertiser()
                ->select([
                    'users.id',
                    DB::raw('COUNT(users_advertisers_domains.advertiser_id) AS all_domains'),
                    DB::raw('COUNT(IF(users_advertisers_domains.id IS NOT NULL AND users_advertisers_domains.approved_at IS NULL, 1, NULL)) AS pending_domains')
                ])
                ->active()
                ->leftjoin('users_advertisers_domains', 'users_advertisers_domains.advertiser_id', '=', 'users.id');
        } else {
            $query = User::asPublisher()
                ->select([
                    'users.id',
                    DB::raw('COUNT(users_publishers_domains.publisher_id) AS all_domains'),
                    DB::raw('COUNT(IF(users_publishers_domains.id IS NOT NULL AND users_publishers_domains.approved_at IS NULL, 1, NULL)) AS pending_domains')
                ])
                ->active()
                ->leftjoin('users_publishers_domains', 'users_publishers_domains.publisher_id', '=', 'users.id');
        }
        $query->groupBy('users.id');
        $users = $this->searchUsers($query, $key)->page($request->query->get('page'));

        $info = User::select(['id', 'name', 'type'])->whereIn('id', $users->pluck('id')->toArray())->get()->groupBy('id');
        $users->each(function ($user) use ($info) {
            $u = $info[$user->id]->first();
            $user->name = $u->name;
            $user->type = $u->type;
        });
        return view('components.list.body', [
            'url' => route('admin.domains.listUsers', compact('key'), false),
            'query' => json_encode($request->all(), JSON_FORCE_OBJECT),
            'header' => ['Owner', 'Approved Domains', 'Pending Domains', 'Actions'],
            'rows' => $users->getCollection()->toString(fn($user) => $this->userListRow($user)->render()),
            'pagination' => $users->links()
        ]);
    }

    private function searchUsers(Builder $q, $key): Builder {
        if ($this->isSearch()) {
            $request = request();

            $this->whereEquals('users.id', $request->user, $q);
            $this->whereString($key === 'advertisers' ? 'users_advertisers_domains.domain' : 'users_publishers_domains.domain', $request->domain, $q);

            if (isset($request->category)) {
                if ($request->category === '0') {
                    $this->whereNull('users_publishers_domains.category_id', true, $q);
                } else {
                    $this->whereEquals('users_publishers_domains.category_id', $request->category, $q);
                }
            }

        } else {
            $q->having('pending_domains', '>', 0);
        }
        return $q;
    }

    private function userListRow(User $user) {
        $url = $user->isAdvertiser() ? route('admin.advertisers.domains.index', ['advertiser' => $user->id], false)
            : route('admin.publishers.domains.index', ['publisher' => $user->id], false);
        return view('components.list.row', [
            'id' => $user->id,
            'columns' => [
                $user->name,
                view('components.list.row-badge', ['class' => 'badge-info', 'value' => $user->all_domains])->render(),
                view('components.list.row-badge', ['class' => $user->pending_domains > 0 ? 'badge-danger' : 'badge-info', 'value' => $user->pending_domains])->render()
            ],
            'extra' => view('components.list.row-action', [
                'click' => 'Ads.item.openExtra(this)',
                'title' => 'Domains',
                'icon' => 'domain',
                'url' => $url
            ])
        ]);
    }

    private function usersSearchForm(string $key) {
        $users_options = User::active()->{$key === 'advertisers' ? 'asAdvertiser' : 'asPublisher'}()->get()->toString(fn($user) => Helper::option($user->id, $user->name));
        $category_options = null;
        if ($key === 'publishers') {
            $category_options = collect([Helper::option(0, 'Without Category', true)])
                ->merge(Category::active()->get()->map(fn($category) => Helper::option($category->id, $category->title)))
                ->toString();
        }
        return view('components.domains.users-search', compact('key', 'users_options', 'category_options'));
    }

    private function isPublishersManager(): bool {
        /** @var User $user */
        $user = auth()->user();
        return ($user->isAdmin() || $user->isManager()) && User::hasPermission('publishers', 'Domains');
    }

    private function isAdvertisersManager(): bool {
        /** @var User $user */
        $user = auth()->user();
        return ($user->isAdmin() || $user->isManager()) && User::hasPermission('advertisers', 'Domains');
    }

    private function requirePermission() {
        /** @var User $user */
        $user = auth()->user();
        if (!$user->isAdmin()) {
            if ($user->isManager()) {
                if (
                    ($this->isPublisher() && !User::hasPermission('publishers', 'Domains'))
                    ||
                    ($this->isAdvertiser() && !User::hasPermission('advertisers', 'Domains'))
                ) {
                    abort(403);
                }
            } else {
                if (
                    ($this->isPublisher() && !$user->isPublisher())
                    ||
                    ($this->isAdvertiser() && !$user->isAdvertiser())
                ) {
                    abort(403);
                }
            }
        }
    }
}
