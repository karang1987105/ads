<?php

namespace App\Http\Controllers\Ads;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Ad;
use App\Models\Campaign;
use App\Models\CampaignTracking;
use App\Models\Category;
use App\Models\Country;
use App\Models\Invoice;
use App\Models\InvoiceCampaign;
use App\Models\User;
use App\Notifications\UserUpdate;
use Arr;
use Auth;
use DB;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Str;

class CampaignsController extends Controller {

    private bool $isAdmin;

    public function __construct() {
        $this->middleware(function ($request, $next) {
            self::requirePermission(self::ANY);

            $this->isAdmin = auth()->user()->isAdmin();

            return $next($request);
        });
    }

    public function index(Ad $ad, Request $request) {
        $headerData = [
            'title' => 'Available Campaigns',
            'search' => $this->searchForm($ad)
        ];
        if (($this->isAdmin && ($ad->isThirdParty() || $ad->isAdminsAd()))
            || (self::checkPermission(self::CREATE) && $ad->advertiser?->getBalance() > 0)) {
            $headerData['add'] = '<form data-name="add" class="d-none"></form>';
            $headerData['add_url'] = route('admin.ads.campaigns.create', compact('ad'), false);
        }

        $list = view('components.list.list', [
            'key' => 'all',
            'header' => view('components.list.header', $headerData),
            'body' => $this->list($ad, $request)
        ]);

        if ($request->has('single')) {
            return view('layouts.app', [
                'page_title' => 'Campaigns',
                'title' => 'Campaigns',
                'slot' => $list
            ]);
        }

        return $list;
    }

    public function list(Ad $ad, Request $request) {
        $campaigns = $this->search($ad->campaigns()->getQuery())
            ->with('invoices')
            ->page($request->query->get('page'));

        return view('components.list.body', [
            'url' => route('admin.ads.campaigns.list', ['ad' => $ad->id], false),
            'query' => json_encode($request->all(), JSON_FORCE_OBJECT),
            'header' => ['Category', 'Current Balance', 'Target Device', 'Revenue Ratio', 'Active', 'Actions'],
            'rows' => $campaigns->getCollection()->toString(fn($campaign) => $this->listRow($campaign)->render()),
            'pagination' => $campaigns->links()
        ]);
    }

    private function search(Builder $q): Builder {
        $req = request();
        if ($this->isSearch()) {
            $this->whereEquals('campaigns.category_id', $req->category, $q);
            $this->whereEquals('campaigns.device', $req->device, $q);
            if ($req->get('active') !== null) {
                if ($req->boolean('active')) {
                    $q->where('campaigns.enabled', '=', true);
                } else {
                    $q->whereNested(function ($qq) {
                        $qq->where('campaigns.enabled', '=', false);
                    });
                }
            }
        } elseif (!empty($req->get('single'))) {
            $q->where('campaigns.id', '=', $req->get('single'));
        }
        return $q;
    }

    protected function listRow(Campaign $campaign) {
        $active = $campaign->isActive();
        $enabled = $campaign->enabled === true;

        $data = [
            'id' => $campaign->id,
            'columns' => [
                $campaign->category->title,
                Helper::amount($campaign->getBalance()),
                $campaign->device,
                $campaign->revenue_ratio,
                $enabled && $active ? '<a class="approved">✔</a>' : ($enabled ? 'Enabled' : '<a class="declined">✘</a>') . ($active ? '' : ', Stopped')
            ],
            'show' => ['url' => route('admin.ads.campaigns.show', ['campaign' => $campaign->id], false)],
            'extra' => []
        ];
        if (self::checkPermission(self::UPDATE)) {
            $data['edit'] = ['url' => route('admin.ads.campaigns.edit', ['campaign' => $campaign->id], false)];
        }
        if (self::checkPermission(self::DELETE)) {
            $data['delete'] = ['url' => route('admin.ads.campaigns.destroy', ['campaign' => $campaign->id], false)];
        }
        if (self::checkPermission([self::APPROVE, self::UNAPPROVE])) {
            if (self::checkPermission(self::UNAPPROVE)) {
                if ($enabled) {
                    $data['extra'][] = view('components.list.row-action', [
                        'click' => 'Ads.item.updateRow(this)',
                        'title' => 'Stop',
                        'icon' => 'stop',
                        'url' => route('admin.ads.campaigns.enable', ['campaign' => $campaign->id, 'enable' => 0], false)
                    ])->render();
                }
            }
            if (self::checkPermission(self::APPROVE)) {
                if (!$enabled) {
                    $data['extra'][] = view('components.list.row-action', [
                        'click' => 'Ads.item.updateRow(this)',
                        'title' => 'Start',
                        'icon' => 'play_arrow',
                        'url' => route('admin.ads.campaigns.enable', ['campaign' => $campaign->id, 'enable' => 1], false)
                    ])->render();
                }
            }
        }

        return view('components.list.row', $data);
    }

    public function show(Campaign $campaign) {
        $amount = $campaign->invoices->sum('amount');
        $current = $campaign->invoices->sum('current');
        return view('components.list.row-details', [
            'rows' => [
                ['caption' => 'Campaign ID:', 'value' => $campaign->id],
                ['caption' => 'Category:', 'value' => $campaign->category->title],
                ['caption' => 'Current Balance:', 'value' => Helper::amount($amount - $current)],
                ['caption' => 'Target Device:', 'value' => $campaign->device],
                ['caption' => 'Revenue Ratio:', 'value' => $campaign->revenue_ratio],
                ['caption' => 'VPN Traffic Allowerd:', 'value' => $campaign->proxy === true ? '<a class="approved">✔</a>' : '<a class="declined">✘</a>'],
                ['caption' => 'Active:', 'value' => $campaign->enabled === true ? '<a class="approved">✔</a>' : '<a class="declined">✘</a>'],
            ]
        ]);
    }

    private function form(Ad $ad, Campaign $campaign = null) {
        $kind = strtolower($ad->adType->kind);
        $categories = Category::query();
        if (!auth()->user()->isAdmin()) {
            $categories = $categories->active();
        }
        $category_options = $categories->with('countries')->get()
            ->toString(function ($c) use ($campaign, $kind) {
                $countriesJson = $c->countries
                    ->filter(fn($country) => $country->country->category !== 'Tier 4')
                    ->mapWithKeys(fn($country) => [$country->country_id => $country->$kind])
                    ->toJson();
                $attrs = [
                    'data-cost' => $c->$kind,
                    'data-countries' => base64_encode($countriesJson),
                    'data-tier4' => $c->countries->filter(fn($country) => $country->country->category === 'Tier 4')->avg($kind)
                ];
                return Helper::option($c->id, $c->title, isset($campaign) && $campaign->category_id === $c->id, $attrs);
            });

        $all = ['Tier 1' => true, 'Tier 2' => true, 'Tier 3' => true, 'Tier 4' => true];
        $current = $campaign !== null ? $campaign->countries->pluck('country_id')->toArray() : [];
        $tiers = [];
        $countries = Country::visible()->get()->groupBy('category')->sortKeys();
        foreach ($countries as $tier => $countryList) {
            if ($tier !== 'Tier 4') {
                $tiers[$tier] = [];
                foreach ($countryList as $country) {
                    $checked = in_array($country->id, $current);
                    $tiers[$tier][] = ['id' => $country->id, 'name' => $country->name, 'checked' => $checked];
                    $all[$tier] = $all[$tier] && $checked;
                }
            } else {
                foreach ($countryList as $country) {
                    if (!in_array($country->id, $current)) {
                        $all['Tier 4'] = false;
                        break;
                    }
                }
            }
        }

        if ($ad->adType->device === 'All') {
            $devices = [['value' => 'Mobile'], ['value' => 'Desktop'], ['value' => 'All']];
        } else {
            $devices = [['value' => $ad->adType->device]];

            if (isset($campaign) && $campaign->device != $ad->adType->device) {
                $devices[] = ['value' => $campaign->device]; // Option to keep the device
            }
        }

        //$ics = isset($campaign) ? InvoiceCampaign::where('campaign_id', '=', $campaign->id)->get() : collect();
        //$currBudget = $ics->sum('amount');
        $budget = [
            'start' => 0//$currBudget
        ];

        if (!$ad->isThirdParty() && !$ad->isAdminsAd()) {
            $budget['min'] = 0;//$ics->sum('current');
            $budget['max'] = /*$currBudget + */
                $ad->advertiser->getBalance();
        }

        $hasEnabledField = $hasStopField = null;
        if ($campaign === null) {
            if (self::checkPermission([self::APPROVE, self::UNAPPROVE])) {
                $hasEnabledField = $hasStopField = true;
            }
        } else {
            if (self::checkPermission(self::UNAPPROVE)) {
                if ($campaign->enabled === true) {
                    $hasEnabledField = true;
                }
                if ($campaign->isActive()) {
                    $hasStopField = true;
                }
            }
            if (self::checkPermission(self::APPROVE)) {
                if ($campaign->enabled !== true) {
                    $hasEnabledField = true;
                }
                if (!$campaign->isActive()) {
                    $hasStopField = true;
                }
            }
        }

        return view('components.ads.campaigns-form', compact('ad', 'campaign', 'category_options',
            'tiers', 'all', 'budget', 'hasEnabledField', 'hasStopField', 'devices'));
    }

    private function searchForm(Ad $ad) {
        $categories = Category::query();
        if (!auth()->user()->isAdmin()) {
            $categories = $categories->active();
        }
        $category_options = $categories->get()->toString(fn($c) => Helper::option($c->id, $c->title));
        return view('components.ads.campaigns-search-form', compact('ad', 'category_options'));
    }

    public function create(Ad $ad) {
        self::requirePermission(self::CREATE);
        return $this->form($ad);
    }

    public function edit(Campaign $campaign) {
        self::requirePermission($campaign != null ? self::UPDATE : self::CREATE);
        return $this->form($campaign->ad, $campaign);
    }

    public function destroy(Campaign $campaign) {
        self::requirePermission(self::DELETE);
        return DB::transaction(function () use ($campaign) {
            $campaign->invoices()->update(['amount' => DB::raw('`current`')]);
            return $campaign->delete();
        });
    }

    public function store(Ad $ad, Request $request) {
        self::requirePermission(self::CREATE);

        if (!self::checkPermission(self::APPROVE)) {
            $request->enabled = null;
        }
        if (!self::checkPermission(self::UNAPPROVE)) {
            $request->stop = null;
        }

        $validator = Validator::make($request->all(), [
            'device' => 'required|in:' . ($ad->adType->device === 'All' ? 'Mobile,Desktop,All' : $ad->adType->device),
            'revenue_ratio' => 'required|between:-9.9999,9.9999',
            'category_id' => 'required|exists:App\Models\Category,id',
            'budget' => 'numeric|required'
        ]);
        if ($validator->fails()) {
            return $this->failure($validator->errors());
        }
        try {
            /** @var Campaign $campaign */
            $campaign = DB::transaction(function () use ($request, $ad) {
                /** @var Campaign $campaign */
                $campaign = $ad->campaigns()->create([
                    'uuid' => Str::uuid()->toString(),
                    'device' => $request->device,
                    'revenue_ratio' => $request->revenue_ratio,
                    'category_id' => $request->category_id,
                    'enabled' => $request->boolean('enabled'),
                    'proxy' => $request->boolean('proxy'),
                    'stopped_at' => $request->boolean('stop') ? now() : null,
                    'stopped_by_id' => $request->boolean('stop') ? Auth::id() : null,
                ]);

                (new CampaignsCountriesController)->insert($campaign, $request);

                self::increaseBudget($campaign, $request->budget);

                return $campaign;
            });
            return $this->success($this->listRow($campaign->fresh())->render());
        } catch (Exception $e) {
            return $this->exception($e);
        }
    }

    public function update(Campaign $campaign, Request $request) {
        self::requirePermission(self::UPDATE);

        $request['proxy'] = $request->boolean('proxy');

        $targetDevice = $campaign->ad->adType->device;
        $devices = $targetDevice === 'All' ? 'Mobile,Desktop,All'
            : $targetDevice . ($targetDevice !== $campaign->device ? ',' . $campaign->device : '');

        $validator = Validator::make($request->all(), [
            'device' => 'required|in:' . $devices,
            'revenue_ratio' => 'required|between:-9.9999,9.9999',
            'category_id' => 'required|exists:App\Models\Category,id',
        ]);
        if ($validator->fails()) {
            return $this->failure($validator->errors());
        }

        $request['enabled'] = $request->boolean('enabled');
        if ($campaign->enabled === true && !$request['enabled']) {
            if (!self::checkPermission(self::UNAPPROVE)) {
                $request->request->remove('enabled');
            }
        }
        if ($campaign->enabled === null && $request['enabled']) {
            if (!self::checkPermission(self::APPROVE)) {
                $request->request->remove('enabled');
            }
        }

//        if ($campaign->isActive() && $request->stop) {
//            if (self::checkPermission(self::UNAPPROVE)) {
//                $request['stopped_by_id'] = Auth::id();
//                $request['stopped_at'] = now();
//            }
//        } elseif (!$campaign->isActive() && !$request->stop) {
//            if (self::checkPermission(self::APPROVE)) {
//                $request['stopped_by_id'] = null;
//                $request['stopped_at'] = null;
//            }
//        }

        try {
            DB::transaction(function () use ($campaign, $request) {
                $campaign->update($request->all());

                (new CampaignsCountriesController)->insert($campaign, $request);

                if (!empty($request->get('budget'))) {
                    self::increaseBudget($campaign, $request->budget);
                }
            });
            return $this->success($this->listRow($campaign->fresh())->render());
        } catch (Exception $e) {
            return $this->exception($e);
        }
    }

    public function enable(Campaign $campaign, $enable) {
        self::requirePermission($enable ? self::APPROVE : self::UNAPPROVE);

        $campaign->update(['enabled' => $enable]);
        return $this->listRow($campaign);
    }

//    public function stop(Campaign $campaign, $stop) {
//        self::requirePermission($stop ? self::UNAPPROVE : self::APPROVE);
//
//        if ($stop && $campaign->isActive()) {
//            $campaign->stopped_by_id = Auth::id();
//            $campaign->stopped_at = now();
//        } elseif (!$stop && !$campaign->isActive()) {
//            $campaign->stopped_by_id = null;
//            $campaign->stopped_at = null;
//        }
//        $campaign->save();
//        return $this->listRow($campaign);
//    }

    /**
     * @throws Exception
     */
    public static function increaseBudget(Campaign $campaign, float $newBudget): void {
        $ics = InvoiceCampaign::where('campaign_id', '=', $campaign->id)->get();

        if ($campaign->ad->isThirdParty() || $campaign->ad->isAdminsAd()) {
            if ($ics->isEmpty()) {

                $invoice = DB::transaction(function () use ($newBudget, $campaign) {
                    $invoice = Invoice::create([
                        'title' => ($campaign->ad->isThirdParty() ? 'Third Party' : "Admin's") . ' Campaign#' . $campaign->id,
                        'user_id' => auth()->id(),
                        'amount' => $newBudget
                    ]);

                    InvoiceCampaign::create([
                        'invoice_id' => $invoice->id,
                        'campaign_id' => $campaign->id,
                        'amount' => $newBudget
                    ]);

                    return $invoice;
                });

                $invoice->balance = $newBudget;

            } else {
                $invoice = $ics[0]->invoice; // There should only ONE invoice for this campaign
                $invoice->increment('amount', $newBudget);
                $ics[0]->increment('amount', $newBudget);

                $invoice->balance += $newBudget;
            }

            return;
        }

        $max = $campaign->ad->advertiser->getBalance();

        if ($newBudget >= 0 && $newBudget <= $max) {
            $diff = $newBudget;

            // Fill current InvoiceCampaigns if possible
            if ($campaign->invoices->isNotEmpty()) {

                /** @var InvoiceCampaign $ic $value */
                foreach ($ics as $ic) {
                    $value = min($diff, $ic->invoice->getBalanceOfCampaign($campaign));
                    $ic->increment('amount', $value);

                    $diff -= $value;
                    if (empty($diff)) {
                        break;
                    }
                }
            }

            // Create new InvoiceCampaigns if there is more
            if ($diff > 0) {
                $invoices = $campaign->ad->advertiser->getUnbalancedInvoices();

                foreach ($invoices as $invoice) {
                    $value = min($diff, $invoice->balance);

                    InvoiceCampaign::create([
                        'invoice_id' => $invoice->id,
                        'campaign_id' => $campaign->id,
                        'amount' => $value
                    ]);

                    $diff -= $value;
                    if (empty($diff)) {
                        break;
                    }
                }
            }

            return;
        }

        throw new Exception("Budget is not in a valid range.");
    }

    public static function cleanupTracking() {
        CampaignTracking::where('time', '<' (time() - config('ads.ads.throttle') * 60))->delete();
    }

    public static function notifyExpiredCampaigns() {
        $mileSize = config('ads.cpm_mile_size');
        $expiredCampaigns = Campaign::query()
            ->select(['campaigns.id', 'users.id AS user'])
            ->join('ads', 'ads.id', '=', 'campaigns.ad_id')
            ->join('ads_types', 'ads_types.id', '=', 'ads.ad_type_id')
            ->join('campaigns_countries', 'campaigns_countries.campaign_id', '=', 'campaigns.id')
            ->join('categories', 'categories.id', '=', 'campaigns.category_id')
            ->leftjoin('categories_countries', 'categories_countries.category_id', '=', 'categories.id')
            ->joinSub('SELECT campaign_id, SUM(amount-current) AS balance FROM invoices_campaigns GROUP BY campaign_id',
                'invoices_campaigns', 'invoices_campaigns.campaign_id', '=', 'campaigns.id')
            ->join('users', function ($j) {
                $j->whereRaw('IF(ads.advertiser_id IS NOT NULL, users.id=ads.advertiser_id, users.id=?)')->addBinding(User::admin()->id);
                $j->whereNotNull('users.active');
            })
            ->where('campaigns.notification_sent', '=', false)
            ->whereRaw("invoices_campaigns.balance < "
                . "(CASE"
                . "    WHEN ads_types.kind='CPC' THEN IFNULL(categories_countries.cpc, categories.cpc)"
                . "    WHEN ads_types.kind='CPV' THEN IFNULL(categories_countries.cpv, categories.cpv)"
                . "    ELSE ROUND(IFNULL(categories_countries.cpm, categories.cpm) / $mileSize, 9)"
                . "END)"
            )
            ->groupBy('campaigns.id', "users.id")
            ->get();

        $users = collect();
        foreach ($expiredCampaigns as $expiredCampaign) {
            $user = $users->getOrPut($expiredCampaign->user, fn() => User::find($expiredCampaign->user));
            $campaign = Campaign::find($expiredCampaign->id);
            if (isset($user, $campaign)) {
                $campaign->update([
                    'enabled' => false,
                    'notification_sent' => true
                ]);
                $user->notifyUser(UserUpdate::$TYPE_CAMPAIGN_EXPIRED, ['campaign' => $campaign]);
            }
        }
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

    private static function checkAllPermissions(array $permission): bool {
        return User::hasAllPermissions('advertisements', $permission);
    }

    private static function requirePermission(string $permission) {
        if (!self::checkPermission($permission)) {
            abort(403);
        }
    }
}
