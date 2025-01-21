<?php

namespace App\Http\Controllers\Ads;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Ad;
use App\Models\Campaign;
use App\Models\Category;
use App\Models\Country;
use DB;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Str;

class CampaignsLimitedController extends Controller {
    public function index(Ad $ad, Request $request) {
        return view('components.list.list', [
            'key' => 'all',
            'header' => view('components.list.header', [
                'title' => 'Available Campaigns',
                'search' => $this->searchForm($ad),
                'add' => '<form data-name="add" class="d-none"></form>',
                'add_url' => route('advertiser.ads.campaigns.create', compact('ad'), false)
            ]),
            'body' => $this->list($ad, $request)
        ]);
    }

    public function list(Ad $ad, Request $request) {
        $campaigns = $this->search($ad->campaigns()->with('invoices')->getQuery())->page($request->query->get('page'));
        return view('components.list.body', [
            'url' => route('advertiser.ads.campaigns.list', ['ad' => $ad->id], false),
            'query' => json_encode($request->all(), JSON_FORCE_OBJECT),
            'header' => ['Category', 'Current Balance', 'Target Device', 'VPN Traffic Allowed', 'Active', 'Actions'],
            'rows' => $campaigns->getCollection()->toString(fn($campaign) => $this->listRow($campaign)->render()),
            'pagination' => $campaigns->links()
        ]);
    }

    private function search(Builder $q): Builder {
        if ($this->isSearch()) {
            $req = request();
            $this->whereEquals('campaigns.category_id', $req->category, $q);
            $this->whereEquals('campaigns.device', $req->device, $q);
            if (isset($req->active)) {
                if (!!$req->active) {
                    $q->where('campaigns.enabled', '=', true);
                    $q->whereNull('campaigns.stopped_at');
                } else {
                    $q->whereNested(function ($qq) {
                        $qq->where('campaigns.enabled', '=', false);
                        $qq->orWhere('campaigns.stopped_at', '!=', null);
                    });
                }
            }
        }
        return $q;
    }

    protected function listRow(Campaign $campaign) {
        $active = $campaign->isActive();
        $enabled = $campaign->enabled === true;
        return view('components.list.row', [
            'id' => $campaign->id,
            'columns' => [
                $campaign->category->title,
                Helper::amount($campaign->getBalance()),
                $campaign->device,
				$campaign->proxy === true ? '<a class="approved">✔</a>' : '<a class="declined">✘</a>',
                $enabled && $active ? '<a class="approved">✔</a>' : ($enabled ? 'Enabled' : '<a class="declined">✘</a>') . ($active ? '' : ', Stopped')
            ],
            'show' => ['url' => route('advertiser.ads.campaigns.show', ['campaign' => $campaign->id], false)],
            'edit' => ['url' => route('advertiser.ads.campaigns.edit', ['campaign' => $campaign->id], false)],
            'delete' => ['url' => route('advertiser.ads.campaigns.destroy', ['campaign' => $campaign->id], false)],
            'extra' =>
                view('components.list.row-action', [
                    'click' => 'Ads.item.updateRow(this)',
                    'title' => $enabled ? 'Stop' : 'Start',
                    'icon' => $enabled ? 'stop' : 'play_arrow',
                    'url' => route('advertiser.ads.campaigns.enable', ['campaign' => $campaign->id, 'enable' => (int)!$enabled], false)
                ])->render()
        ]);
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
                ['caption' => 'VPN Traffic Allowed:', 'value' => $campaign->proxy === true ? '<a class="approved">✔</a>' : '<a class="declined">✘</a>'],
                ['caption' => 'Active:', 'value' => $campaign->enabled === true ? '<a class="approved">✔</a>' : '<a class="declined">✘</a>'],
            ]
        ]);
    }

    private function form(Ad $ad, Campaign $campaign = null) {
        $userBalance = $ad->advertiser->getBalance();
        $editable = $userBalance > 0;

        $kind = strtolower($ad->adType->kind);
        $category_options = Category::active()->with('countries')->get()
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

            if (isset($campaign) && $campaign->device !== $ad->adType->device) {
                $devices[] = ['value' => $campaign->device]; // Option to keep the device
            }
        }

        return view('components.ads.campaigns-limited-form', compact(
            'ad',
            'campaign',
            'category_options',
            'tiers',
            'all',
            'devices',
            'editable'
        ));
    }

    private function searchForm(Ad $ad) {
        $category_options = Category::active()->get()->toString(fn($c) => Helper::option($c->id, $c->title));
        return view('components.ads.campaigns-limited-search-form', compact('ad', 'category_options'));
    }

    public function create(Ad $ad) {
        return $this->form($ad);
    }

    public function edit(Campaign $campaign) {
        return $this->form($campaign->ad, $campaign);
    }

    public function destroy(Campaign $campaign) {
        return DB::transaction(function () use ($campaign) {
            $campaign->invoices()->update(['amount' => DB::raw('`current`')]);
            return $campaign->delete();
        });
    }

    public function store(Ad $ad, Request $request) {
        $validator = Validator::make($request->all(), [
            'device' => 'required|in:' . ($ad->adType->device === 'All' ? 'Mobile,Desktop,All' : $ad->adType->device),
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
                    'category_id' => $request->category_id,
                    'enabled' => isset($request->enabled),
                    'proxy' => isset($request->proxy),
                ]);

                (new CampaignsCountriesController)->insert($campaign, $request);

                CampaignsController::increaseBudget($campaign, $request->budget);

                return $campaign;
            });
            return $this->success($this->listRow($campaign->fresh())->render());
        } catch (Exception $e) {
            return $this->exception($e);
        }
    }

    public function update(Campaign $campaign, Request $request) {
        $targetDevice = $campaign->ad->adType->device;
        $devices = $targetDevice === 'All' ? 'Mobile,Desktop,All'
            : $targetDevice . ($targetDevice != $campaign->device ? ',' . $campaign->device : '');

        $validator = Validator::make($request->all(), [
            'device' => 'required|in:' . $devices,
            'category_id' => 'required|exists:App\Models\Category,id',
        ]);
        if ($validator->fails()) {
            return $this->failure($validator->errors());
        }

        $request['enabled'] = isset($request->enabled);
        $request['proxy'] = isset($request->proxy);

        try {
            DB::transaction(function () use ($campaign, $request) {
                $campaign->update($request->all());

                (new CampaignsCountriesController)->insert($campaign, $request);

                if (!empty($request->get('budget'))) {
                    CampaignsController::increaseBudget($campaign, $request->budget);
                }
            });
            return $this->success($this->listRow($campaign->fresh())->render());
        } catch (Exception $e) {
            return $this->exception($e);
        }
    }

    public function enable(Campaign $campaign, $enable) {
        $campaign->update(['enabled' => $enable]);
        return $this->listRow($campaign);
    }
}
