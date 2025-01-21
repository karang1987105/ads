<?php

namespace App\Http\Controllers\Places;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\AdType;
use App\Models\Place;
use App\Models\User;
use App\Models\UserPublisher;
use App\Notifications\UserUpdate;
use Auth;
use DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Str;

class PlacesController extends Controller {

    public function __construct() {
        $this->middleware(function ($request, $next) {
            if (!User::hasPermission('publishers', 'Places')) {
                abort(403);
            }

            return $next($request);
        });
    }

    public function publishersIndex(Request $request) {
        return view('layouts.app', [
            'page_title' => 'Manage Places',
            'slot' =>
                view('components.list.list', [
                    'key' => 'pending',
                    'header' => view('components.list.header', ['title' => 'Pending Places', 'search' => $this->searchForm('pending')]),
                    'body' => $this->publishersList('pending', $request)
                ])->render()
                . view('components.list.list', [
                    'key' => 'all',
                    'header' => view('components.list.header', ['title' => 'Existing Places', 'search' => $this->searchForm('all')]),
                    'body' => $this->publishersList('all', $request)
                ])->render()
        ]);
    }

    public function index(UserPublisher $publisher, Request $request) {
        return view('components.list.list', [
            'key' => 'all',
            'header' => view('components.list.header', [
                'title' => 'Existing Places',
                'add' => '<form data-name="add" class="d-none"></form>',
                'add_url' => route('admin.places.create', ['publisher' => $publisher->user_id], false)
            ]),
            'body' => $this->list($publisher, $request)
        ]);
    }

    public function publishersList($key, Request $request) {
        if ($key === 'all' || $key === 'pending') {
            $builder = $this->searchPublisher($key, UserPublisher::query())->active()->withCount([
                'places AS approved_places' => fn($q) => $q->whereNotNull('places.approved_at'),
                'places AS pending_places' => fn($q) => $q->whereNull('places.approved_at'),
            ]);
            $publishers = $builder->page($request->query->get('page'));

            $rows = $publishers->getCollection()->map(function ($publisher) {
                $badges = view('components.list.row-badge', ['class' => 'badge-info', 'value' => $publisher->approved_places])->render();
                if ($publisher->pending_places > 0) {
                    $badges .= view('components.list.row-badge', ['class' => 'badge-danger', 'value' => $publisher->pending_places])->render();
                }
                return view('components.list.row', [
                    'id' => $publisher->id,
                    'columns' => [$publisher->user->name, $publisher->user->email, $badges],
                    'extra' => view('components.list.row-action', [
                        'click' => 'Ads.item.openExtra(this)',
                        'title' => "Places",
                        'icon' => "developer_mode",
                        'url' => route('admin.places.index', ['publisher' => $publisher->user_id], false)
                    ])
                ])->render();
            })->join('');

            return view('components.list.body', [
                'url' => route('admin.places.publishers-list', ['key' => $key], false),
                'query' => json_encode($request->all(), JSON_FORCE_OBJECT),
                'header' => ['Owner', 'Email Address', 'Place Count', 'Actions'],
                'rows' => $rows,
                'pagination' => $publishers->links()
            ]);
        }

        abort(422);
    }

    private function searchPublisher($key, Builder $q): Builder {
        $req = request();
        if ($this->isSearch()) {
            $this->whereEquals('users_publishers.user_id', $req->user, $q);

            if ($req->has('domain') || $req->has('ad_type') || $req->has('title')) {
                $q->whereExists(function ($qq) use ($key, $req) {
                    $qqq = $qq->select(DB::raw(1))->from('places')
                        ->join('users_publishers_domains', function ($qqqq) use ($req) {
                            $qqqq->whereColumn('users_publishers_domains.id', '=', 'places.domain_id');
                            $qqqq->whereColumn('users_publishers_domains.publisher_id', '=', 'users_publishers.user_id');

                            if ($req->has('domain')) {
                                $qqqq->where('users_publishers_domains.domain', 'like', '%' . str_replace(' ', '%', $req->get('domain')) . '%');
                            }
                        });

                    if ($req->has('ad_type')) {
                        $qqq->where('places.ad_type_id', '=', $req->ad_type);
                    }

                    if ($req->has('title')) {
                        $this->whereString('places.title', $req->title, $qqq);
                    }

                    if ($key === 'pending') {
                        $qqq->whereNull('places.approved_at');
                    }
                });
            }
        } else {
            $q->whereHas('places', function ($qq) use ($key) {
                $qq->whereNull('places.deleted_at');

                if ($key === 'pending') {
                    $qq->whereNull('places.approved_at');
                }

                return $qq;
            });
        }

        return $q;
    }

    public function list(UserPublisher $publisher, Request $request) {
        $places = $publisher->places()->page($request->query->get('page'), ['places.*']);
        $rows = $places->getCollection()->toString(fn($place) => $this->listRow($place)->render());
        return view('components.list.body', [
            'url' => route('admin.places.list', ['publisher' => $publisher->user_id], false),
            'query' => json_encode($request->all(), JSON_FORCE_OBJECT),
            'header' => ['Title', 'AD Type', 'AD Size', 'Domain', 'Approved', 'Actions'],
            'rows' => $rows,
            'pagination' => $places->links()
        ]);
    }

    protected function listRow(Place $place) {
        $isApproved = $place->isApproved();
        return view('components.list.row', [
            'id' => $place->id,
            'columns' => [
                $place->title,
                $place->adType->name ,
				$place->adType->getSize(),
                $place->domain->domain,
                $isApproved ? '<a class="approved">✔</a>' : '<a class="declined">✘</a>'
            ],
            'show' => ['url' => route('admin.places.show', ['place' => $place->id], false)],
            'edit' => ['url' => route('admin.places.edit', ['place' => $place->id], false)],
            'delete' => ['url' => route('admin.places.destroy', ['place' => $place->id], false)],
            'extra' => view('components.list.row-action', [
                'click' => 'Ads.item.updateRow(this)',
                'title' => $isApproved ? 'Decline' : 'Approve',
                'icon' => $isApproved ? 'block' : 'task_alt',
                'url' => route('admin.places.approve', ['place' => $place->id, 'approve' => intval(!$isApproved)], false)
            ])
        ]);
    }

    public function show(Place $place) {
        return view('components.list.row-details', [
            'rows' => [
                ['caption' => 'Place ID:', 'value' => $place->id],
                ['caption' => 'Domain Category:', 'value' => $place->domain->category->title],
                ['caption' => 'Approved:', 'full' => true, 'value' => $place->isApproved() ? '<a class="approved">✔</a>' : '<a class="declined">✘</a>'],				
                ['caption' => 'Approved By:', 'full' => true, 'value' => $place->isApproved() ? "{$place->approvedBy->user->name} at " . $place->approved_at->format('Y-m-d h:i') : 'Nobody'],
                [
                    'caption' => 'AD Code:',
                    'full' => true,
                    'value' => '<pre>&lt;script id="s_' . md5($place->uuid) . '" src="' . route('scripts.init', ['uuid' => $place->uuid]) . '"&gt;&lt;/script&gt;</pre>'
                ]
            ]
        ]);
    }

    private function form(UserPublisher $publisher, Place $place = null) {
        $adtype_options = AdType::active()->get()
            ->toString(fn($adType) => Helper::option($adType->id, "$adType->name " . $adType->getSize(), isset($place) && $place->ad_type_id === $adType->id));
        $domain_options = $publisher->domains()->whereNotNull('approved_at')->whereNotNull('category_id')->get()
            ->toString(fn($domain) => Helper::option($domain->id, $domain->domain . ' @ Category: ' . $domain->category->title, isset($place) && $place->domain_id === $domain->id));

        $params = [
            'route' => 'admin',
            'publisher' => $publisher,
            'adtype_options' => $adtype_options,
            'domain_options' => $domain_options
        ];
        if ($place != null) {
            $params['place'] = $place;
        }
        return view('components.places.form', $params);
    }

    private function searchForm(string $key) {
        $users_options = User::asPublisher()->get()->toString(fn($user) => Helper::option($user->id, "$user->name"));
        $adtypes_options = AdType::active()->get()
            ->toString(fn($adType) => Helper::option($adType->id, "$adType->name " . $adType->getSize()));
        return view('components.places.admin-search-form', compact('key', 'users_options', 'adtypes_options'));
    }

    public function create(UserPublisher $publisher) {
        return $this->form($publisher);
    }

    public function edit(Place $place) {
        return $this->form($place->publisher, $place);
    }

    public function destroy(Place $place) {
        return $place->delete();
    }

    public function store(Request $request) {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string',
            'domain_id' => 'required|exists:App\Models\Domains\PublisherDomain,id',
            'ad_type_id' => 'required|exists:App\Models\AdType,id',
        ]);

        if ($validator->fails()) {
            return $this->failure($validator->errors());
        }

        $samePlaceExists = Place::query()
            ->where('ad_type_id', $request->get('ad_type_id'))
            ->where('domain_id', $request->get('domain_id'))
            ->whereNull('deleted_at')
            ->exists();

        if ($samePlaceExists) {
            return $this->failure(['form' => 'A place with ad type and domain currently exists.']);
        }

        $place = Place::create([
            'title' => $request->title,
            'domain_id' => $request->domain_id,
            'ad_type_id' => $request->ad_type_id,
            'approved_by_id' => isset($request->approve) ? Auth::id() : null,
            'approved_at' => isset($request->approve) ? now() : null,
            'uuid' => Str::uuid()->toString()
        ]);

        return $this->success($this->listRow($place)->render());
    }

    public function update(Place $place, Request $request) {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string',
            'domain_id' => 'required|exists:App\Models\Domains\PublisherDomain,id',
            'ad_type_id' => 'required|exists:App\Models\AdType,id',
        ]);
        if ($validator->fails()) {
            return $this->failure($validator->errors());
        }

        $samePlaceExists = Place::query()
            ->where('ad_type_id', $request->get('ad_type_id'))
            ->where('domain_id', $request->get('domain_id'))
            ->whereNull('deleted_at')
            ->where('id', '!=', $place->id)
            ->exists();

        if ($samePlaceExists) {
            return $this->failure(['form' => 'A place with ad type and domain currently exists.']);
        }

        if ($request->approve && !$place->isApproved()) {
            $request['approved_by_id'] = Auth::id();
            $request['approved_at'] = now();
        } elseif (!$request['approve'] && $place->isApproved()) {
            $request['approved_by_id'] = null;
            $request['approved_at'] = null;
        }

        $statusChanged = $place->isApproved() != $request->approve;

        $place->update($request->all());

        if ($statusChanged) {
            $place->publisher->user->notifyUser(
                $place->isApproved() ? UserUpdate::$TYPE_PLACE_APPROVED : UserUpdate::$TYPE_PLACE_DECLINED,
                ['place' => $place->title]
            );
        }

        return $this->success($this->listRow($place->fresh())->render());
    }

    public function approve(Place $place, $approve) {
        if ($approve && !$place->isApproved()) {
            $place->approved_by_id = Auth::id();
            $place->approved_at = now();
        } elseif (!$approve && $place->isApproved()) {
            $place->approved_by_id = null;
            $place->approved_at = null;
        }
        $place->save();

        $place->publisher->user->notifyUser(
            $place->isApproved() ? UserUpdate::$TYPE_PLACE_APPROVED : UserUpdate::$TYPE_PLACE_DECLINED,
            ['place' => $place->title]
        );

        return $this->listRow($place);
    }
}
