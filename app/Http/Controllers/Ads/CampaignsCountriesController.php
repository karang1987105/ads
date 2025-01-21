<?php


namespace App\Http\Controllers\Ads;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\CampaignCountry;
use App\Models\Country;
use Auth;
use DB;
use Exception;
use Illuminate\Http\Request;

class CampaignsCountriesController extends Controller {
    public function index(Campaign $campaign) {
        return $this->list($campaign);
    }

    private function list(Campaign $campaign) {
        $route = Auth::user()->isManager() ? 'admin' : 'advertiser';
        $all = ['Tier 1' => true, 'Tier 2' => true, 'Tier 3' => true, 'Tier 4' => true];
        $current = $campaign->countries->pluck('country_id')->toArray();

        $kind = strtolower($campaign->ad->adType->kind);
        $cost = $campaign->category->{$kind};
        $countriesCost = $campaign->category->countries
            ->filter(fn($country) => $country->country->category !== 'Tier 4')
            ->mapWithKeys(fn($country) => [$country->country_id => $country->$kind])
            ->toArray();
        $tier4Cost = $campaign->category->countries
            ->filter(fn($country) => $country->country->category === 'Tier 4')
            ->avg($kind);

        $tiers = [];
        $countries = Country::visible()->get()->groupBy('category')->sortKeys();
        foreach ($countries as $tier => $countryList) {
            if ($tier !== 'Tier 4') {
                $tiers[$tier] = [];
                foreach ($countryList as $country) {
                    $checked = in_array($country->id, $current);
                    $tiers[$tier][] = [
                        'id' => $country->id,
                        'name' => $country->name . ': ' . Helper::amount($countriesCost[$country->id] ?? $cost, 6),
                        'checked' => $checked
                    ];
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
        return view('components.list.list', [
            'key' => 'all',
            'nobody' => true,
            'header' => view('components.list.header', [
                'title' => 'Countries',
                'refresh' => false,
                'slot' => view('components.ads.campaigns-countries-form', [
                    'route' => $route,
                    'campaign' => $campaign->id,
                    'tiers' => $tiers,
                    'all' => $all,
                    'tier4Cost' => Helper::amount($tier4Cost ?? $cost, 6)
                ])
            ])
        ]);
    }

    public function update(Campaign $campaign, Request $request) {
        try {
            $this->insert($campaign, $request);
            return $this->success([
                'list' => $this->list($campaign)->render(),
                'alert' => view('components.page-message', [
                    'class' => 'alert-success',
                    'icon' => 'fa-check',
                    'message' => 'Success! Values has been updated!'
                ])->render()
            ]);
        } catch (Exception $e) {
            return $this->exception($e);
        }
    }

    public function insert(Campaign $campaign, Request $request) {
        $insert = [];
        $inputCountries = array_keys($request->input('countries') ?? []);
        $countries = array_merge($inputCountries, (!!$request->input("tier4") ? Country::tier4()->visible()->get('id')->pluck('id')->toArray() : []));
        foreach ($countries as $country) {
            $insert[] = new CampaignCountry(['country_id' => $country]);
        }
        DB::transaction(function () use ($campaign, $insert) {
            $campaign->countries()->delete();
            $campaign->countries()->saveMany($insert);
        });
    }
}
