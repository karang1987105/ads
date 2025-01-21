<?php

namespace App\Http\Controllers\Ads;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Ad;
use App\Models\AdType;
use App\Models\Setting;
use App\Models\User;
use App\Models\UserAdvertiser;
use Arr;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;

class AdsController extends Controller {
    use AdsTrait;

    public function __construct() {
        $this->middleware(function ($request, $next) {
            self::requirePermission(self::ANY);
            return $next($request);
        });
    }

    public function advertisersIndex(Request $request) {
        $actions = null;
        $adminsLists = '';
        if ($this->isAdmin()) {
            $adminsLists .= view('components.list.list', [
                'key' => 'all',
                'header' => view('components.list.header', ['title' => 'Third Party ADS', 'add' => $this->thirdPartyForm(), 'search' => $this->thirdPartySearchForm()]),
                'body' => $this->advertisersList('thirdparties', $request)
            ])->render();

            $adminsLists .= view('components.list.list', [
                'key' => 'all',
                'header' => view('components.list.header', [
                    'title' => 'System ADS',
                    'add' => $this->form(null)
                ]),
                'body' => $this->list(null, $request)
            ])->render();

            $actions = $this->getStopButton();
        }
        return view('layouts.app', [
            'page_title' => 'Manage ADS',
            'actions' => $actions,
            'slot' =>
                $adminsLists .
                view('components.list.list', [
                    'key' => 'pending',
                    'header' => view('components.list.header', ['title' => 'Pending ADS', 'search' => $this->searchForm('pending')]),
                    'body' => $this->advertisersList('pending', $request)
                ])->render() .
                view('components.list.list', [
                    'key' => 'all',
                    'header' => view('components.list.header', ['title' => 'Existing ADS', 'search' => $this->searchForm('all')]),
                    'body' => $this->advertisersList('all', $request)
                ])->render()
        ]);
    }

    public function index(UserAdvertiser $advertiser, Request $request) {
        $headerData = ['title' => 'Existing ADS', 'search' => $this->adSearchForm($advertiser)];
        if (self::checkPermission(self::CREATE)) {
            $headerData['add'] = $this->form($advertiser);
        }
        return view('components.list.list', [
            'key' => 'all',
            'header' => view('components.list.header', $headerData),
            'body' => $this->list($advertiser, $request)
        ]);
    }

    public function advertisersList($key, Request $request) {
        if ($key === 'all' || $key === 'pending') {

            $builder = $this->searchAdvertisers(UserAdvertiser::query())
                ->withCount([
                    'ads AS approved_ads' => fn($q) => $q->whereNotNull('ads.approved_at'),
                    'ads AS pending_ads' => fn($q) => $q->whereNull('ads.approved_at'),
                ]);

            if (!$this->isSearch()) {
                if ($key === 'pending') {
                    $builder->having('pending_ads', '>', 0);
                } else {
                    $builder->having('pending_ads', '>', 0);
                    $builder->orHaving('approved_ads', '>', 0);
                }
            }
            $advertisers = $builder->page($request->query->get('page'));

            $rows = $advertisers->getCollection()->map(function ($advertiser) {
                $badges = view('components.list.row-badge', ['class' => 'badge-info', 'value' => $advertiser->approved_ads])->render();
                if ($advertiser->pending_ads > 0) {
                    $badges .= view('components.list.row-badge', ['class' => 'badge-danger', 'value' => $advertiser->pending_ads])->render();
                }
                return view('components.list.row', [
                    'id' => $advertiser->id,
                    'columns' => [$advertiser->user->name, $advertiser->user->email, $badges],
                    'extra' => view('components.list.row-action', [
                        'click' => 'Ads.item.openExtra(this)',
                        'title' => "AD List",
                        'icon' => "format_indent_increase",
                        'url' => route('admin.ads.index', ['advertiser' => $advertiser->user_id], false)
                    ])
                ])->render();
            })->join('');

            return view('components.list.body', [
                'url' => route('admin.ads.advertisers-list', ['key' => $key], false),
                'query' => json_encode($request->all(), JSON_FORCE_OBJECT),
                'header' => ['Owner', 'Email Address', 'AD Count', 'Actions'],
                'rows' => $rows,
                'pagination' => $advertisers->links()
            ]);

        } elseif ($key === 'thirdparties' && $this->isAdmin()) {
            $ads = $this->searchThirdParties(Ad::asThirdParty())->page($request->query->get('page'));
            return view('components.list.body', [
                'url' => route('admin.ads.advertisers-list', ['key' => $key], false),
                'query' => json_encode($request->all(), JSON_FORCE_OBJECT),
                'header' => ['Title', 'AD Type', 'AD Size', 'Approved', 'Actions'],
                'rows' => $ads->getCollection()->toString(fn($ad) => $this->thirdPartyListRow($ad)->render()),
                'pagination' => $ads->links()
            ]);

        } else {
            abort(422);
        }
    }

    /** @noinspection StaticInvocationViaThisInspection */
    private function searchAdvertisers(Builder $q): Builder {
        if ($this->isSearch()) {
            $request = request();

            $this->whereEquals('users_advertisers.user_id', $request->user, $q);

            if ($request->ad_type) {
                $q->whereHas('ads', fn($q) => $q->where('ad_type_id', $request->ad_type));
            }

            if ($request->has('domain')) {
                $sub = UserAdvertiser::active()->select(['users_advertisers.user_id']);
                $sub->join('ads', function (JoinClause $join) {
                    $join->whereColumn('ads.advertiser_id', 'users_advertisers.user_id')
                        ->whereNull('ads.deleted_at');
                });
                $sub->leftjoin('ads_banners', 'ads_banners.ad_id', '=', 'ads.id');
                $sub->leftjoin('ads_videos', 'ads_videos.ad_id', '=', 'ads.id');
                $sub->join('users_advertisers_domains', function (JoinClause $subq) use ($request) {
                    $subq->where('users_advertisers_domains.domain', 'like', '%' . str_replace(' ', '%', $request->domain) . '%');
                    $subq->where(function ($subqq) {
                        $subqq->whereColumn('users_advertisers_domains.id', '=', 'ads_banners.domain_id');
                        $subqq->orWhereColumn('users_advertisers_domains.id', '=', 'ads_videos.domain_id');
                    });
                });
                $sub->groupBy('users_advertisers.user_id');
                $q->whereIn('users_advertisers.user_id', $sub->get()->pluck('user_id')->toArray());
            }
        }
        return $q;
    }

    private function searchThirdParties(Builder $q): Builder {
        if ($this->isSearch()) {
            $req = request();
            if ($req->input('title')) {
                $q->whereHas('thirdParty', fn($qq) => $this->whereString('ads_thirdparties.title', $req->title, $qq));
            }
            $this->whereEquals('ads.ad_type_id', $req->ad_type, $q);

        }
        return $q;
    }

    public function list(?UserAdvertiser $advertiser, Request $request) {
        $query = Ad::asNormalAd()->whereAdvertiserId($advertiser?->user_id);
        $ads = $this->search($query)->page($request->query->get('page'));

        return view('components.list.body', [
            'url' => route('admin.ads.list', ['advertiser' => $advertiser?->user_id], false),
            'query' => json_encode($request->all(), JSON_FORCE_OBJECT),
            'header' => ['Title', 'AD Type', 'AD Size',	'Domain', 'Approved', 'Actions'],
            'rows' => $ads->getCollection()->toString(fn($ad) => $this->listRow($ad)->render()),
            'pagination' => $ads->links()
        ]);
    }

    private function search(Builder $q): Builder {
        if ($this->isSearch()) {
            $req = \request();

            $this->whereEquals('ads.ad_type_id', $req->ad_type, $q);

            if ($req->has('ad_type_name')) {
                $q->join('ads_types', function ($qq) use ($req) {
                    $qq->whereColumn('ads_types.id', 'ads.ad_type_id');
                    $this->whereString('ads_types.name', $req->ad_type_name, $qq);
                });
            }

            $this->whereNotNull('ads.approved_at', $req->approved, $q);
            if ($req->has('title')) {
                $q->leftJoin('ads_banners', 'ads_banners.ad_id', '=', 'ads.id');
                $q->leftJoin('ads_videos', 'ads_videos.ad_id', '=', 'ads.id');
                $q->whereNested(function ($qq) use ($req) {
                    $qq->where('ads_banners.title', '=', $req->title);
                    $qq->orWhere('ads_videos.title', '=', $req->title);
                });
            }
        }
        return $q;
    }

    protected function thirdPartyListRow(Ad $ad) {
        $isApproved = $ad->isApproved();
        return view('components.list.row', [
            'id' => $ad->id,
            'columns' => [
			    $ad->thirdParty->title,
                $ad->adType->name,
				$ad->adType->getSize(),
                $isApproved ? '<a class="approved">✔</a>' : '<a class="declined">✘</a>'
            ],
            'show' => ['url' => route('admin.ads.show', ['ad' => $ad->id], false)],
            'edit' => ['url' => route('admin.ads.edit', ['ad' => $ad->id], false)],
            'delete' => ['url' => route('admin.ads.destroy', ['ad' => $ad->id], false)],
            'extra' => [
                view('components.list.row-action', [
                    'click' => 'Ads.item.updateRow(this)',
                    'title' => $isApproved ? 'Decline' : 'Approve',
                    'icon' => $isApproved ? 'block' : 'task_alt',
                    'url' => route('admin.ads.approve', ['ad' => $ad->id, 'approve' => intval(!$isApproved)], false)
                ]),
				view('components.list.row-action', [
                    'click' => 'Ads.item.openExtra(this)',
                    'title' => "Campaigns",
                    'icon' => "campaign",
                    'url' => route('admin.ads.campaigns.index', ['ad' => $ad->id], false)
                ]),
            ]
        ]);
    }

    protected function listRow(Ad $ad) {
        $data = [
            'id' => $ad->id,
            'columns' => [
                $ad->isBanner() ? $ad->banner->title : $ad->video->title,
                $ad->adType->name,
                $ad->adType->getSize(),
                $ad->isBanner() ? $ad->banner->domain->domain : $ad->video->domain->domain,
                $ad->isApproved() ? '<a class="approved">✔</a>' : '<a class="declined">✘</a>',
            ],
            'show' => ['url' => route('admin.ads.show', ['ad' => $ad->id], false)],
            'extra' => []
        ];
        
        if (self::checkPermission(self::UPDATE)) {
            $data['edit'] = ['url' => route('admin.ads.edit', ['ad' => $ad->id], false)];
        }
        
        if (self::checkPermission(self::DELETE)) {
            $data['delete'] = ['url' => route('admin.ads.destroy', ['ad' => $ad->id], false)];
        }
        
        if ($ad->isApproved() && self::checkPermission(self::UNAPPROVE)) {
            $data['extra'][] = view('components.list.row-action', [
                'click' => 'Ads.item.updateRow(this)',
                'title' => 'Decline',
                'icon' => 'block',
                'url' => route('admin.ads.approve', ['ad' => $ad->id, 'approve' => 0], false)
            ]);
        }
        
        if (!$ad->isApproved() && self::checkPermission(self::APPROVE)) {
            $data['extra'][] = view('components.list.row-action', [
                'click' => 'Ads.item.updateRow(this)',
                'title' => 'Approve',
                'icon' => 'task_alt',
                'url' => route('admin.ads.approve', ['ad' => $ad->id, 'approve' => 1], false)
            ]);
        }
        
        $data['extra'][] = view('components.list.row-action', [
            'click' => 'Ads.item.openExtra(this)',
            'title' => "Campaigns",
            'icon' => "campaign",
            'url' => route('admin.ads.campaigns.index', ['ad' => $ad->id], false)
        ]);
        
        return view('components.list.row', $data);
    }

    public function show(Ad $ad) {
        if ($ad->isThirdParty() && !$this->isAdmin()) {
            abort(403);
        }
        return $this->_show($ad);
    }

    private function form(?UserAdvertiser $advertiser, Ad $ad = null) {
        return view('components.ads.form', $this->_formData($advertiser, $ad));
    }

    private function adSearchForm(UserAdvertiser $advertiser) {
        $adtype_options = AdType::/*active()->*/ get()
            ->toString(fn($adType) => Helper::option($adType->id, "$adType->name " . $adType->getSize()));
        return view('components.ads.search-form', compact('advertiser', 'adtype_options'));
    }

    private function searchForm(string $key) {
        $users_options = User::asAdvertiser()->get()->toString(fn($user) => Helper::option($user->id, "$user->name"));
        $adtype_options = AdType::/*active()->*/ get()
            ->toString(fn($adType) => Helper::option($adType->id, "$adType->name " . $adType->getSize()));
        return view('components.ads.advertisers-search-form', compact('key', 'users_options', 'adtype_options'));
    }

    private function thirdPartyForm(Ad $ad = null) {
        $adtype_options = AdType::active()->get()
            ->toString(fn($adType) => Helper::option($adType->id, "$adType->name " . $adType->getSize(), $ad?->ad_type_id === $adType->id));
        return view('components.ads.thirdparty-form', compact('adtype_options') + compact('ad'));
    }

    private function thirdPartySearchForm() {
        $adtype_options = AdType::active()->get()
            ->toString(fn($adType) => Helper::option($adType->id, "$adType->name " . $adType->getSize()));
        return view('components.ads.thirdparties-search-form', ['key' => 'thirdparties', 'adtype_options' => $adtype_options]);
    }

    public function edit(Ad $ad) {
        if ($ad->isThirdParty()) {
            if ($this->isAdmin()) {
                return $this->thirdPartyForm($ad);
            } else {
                abort(403);
            }
        }
        self::requirePermission(self::UPDATE);
        return $this->form($ad->advertiser, $ad);
    }

    public function destroy(Ad $ad) {
        if ($ad->isThirdParty() && !$this->isAdmin()) {
            abort(403);
        }
        self::requirePermission(self::DELETE);
        try {
            return $this->_destroy($ad);
        } catch (Exception $e) {
            return $this->exception($e);
        }
    }

    public function store(Request $request, ?UserAdvertiser $advertiser = null) {
        self::requirePermission(self::CREATE);
        try {
            return $this->_store($request, $advertiser);
        } catch (Exception $e) {
            return $this->exception($e);
        }
    }

    public function update(Ad $ad, Request $request) {
        if ($ad->isThirdParty() && !$this->isAdmin()) {
            abort(403);
        }
        self::requirePermission(self::UPDATE);
        try {
            return $this->_update($ad, $request);
        } catch (Exception $e) {
            return $this->exception($e);
        }
    }

    public function approve(Ad $ad, $approve) {
        if ($ad->isThirdParty() && !$this->isAdmin()) {
            abort(403);
        }

        self::requirePermission($approve ? self::APPROVE : self::UNAPPROVE);
        $this->_approveModel($ad, $approve);

        $ad->refresh();
        return $ad->isThirdParty() ? $this->thirdPartyListRow($ad) : $this->listRow($ad);
    }

    public function changeState() {
        if ($this->isAdmin()) {
            Setting::toggle(Setting::CAMPAIGNS_STOP);
            return $this->getStopButton();
        }
        abort(403);
    }

    private function getStopButton($currentlyStopped = null): string {
        $currentlyStopped = $currentlyStopped ?? !!Setting::value(Setting::CAMPAIGNS_STOP);
        return view('components.action-button', [
            'slot' => $currentlyStopped ? 'resume campaigns' : 'stop campaigns',
            'url' => route('admin.campaigns.change-state'),
            'confirm' => 'Are you sure that you want to ' . ($currentlyStopped ? 'resume' : 'stop') . ' campaigns?'
        ])->render();
    }

    ////////////////////////////////////////////////////
    private const ANY = 'Any';
    private const CREATE = 'Create';
    private const UPDATE = 'Update';
    private const DELETE = 'Delete';
    private const APPROVE = 'Activate';
    private const UNAPPROVE = 'Block';

    private static function checkPermission(string|array $permission): bool {
        return User::hasAnyPermissions('advertisements', $permission === self::ANY ? [self::CREATE, self::UPDATE, self::DELETE, self::APPROVE, self::UNAPPROVE] : Arr::wrap($permission));
    }

    private static function requirePermission(string $permission) {
        if (!self::checkPermission($permission)) {
            abort(403);
        }
    }
}
