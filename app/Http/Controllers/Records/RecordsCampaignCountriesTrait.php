<?php

namespace App\Http\Controllers\Records;

use App\Helpers\Helper;
use App\Models\Campaign;
use App\Models\Country;
use Auth;
use DB;

trait RecordsCampaignCountriesTrait {
    protected function countriesIndex(Campaign|int $campaign, $tier) {
        $campaign = $campaign instanceof Campaign ? $campaign : Campaign::withoutTrashed()->find($campaign);
        if (isset($campaign) && $campaign->deleted_at === null) {
            $campaign->load(['ad' => fn($q) => $q->withTrashed()]);

            return view('components.list.list', [
                'key' => 'ads',
                'header' => view('components.list.header', ['title' => self::TITLE['countries'] . ': ' . $tier]),
                'body' => $this->countriesList($campaign, $tier)
            ]);
        }

        abort(404);
    }

    protected function countriesList(Campaign|int $campaign, $tier) {
        $campaign = $campaign instanceof Campaign ? $campaign : Campaign::withoutTrashed()->find($campaign);
        if (isset($campaign) && $campaign->deleted_at === null) {
            $campaign->load(['ad' => fn($q) => $q->withoutTrashed()]);

            $user = Auth::user();
            if ($user->isManager() || ($user->isAdvertiser() && $campaign->ad->advertiser_id === $user->id)) {
                $req = request();

                $from = request()->get('from');
                $to = request()->get('to');

                $countries = $campaign->records()
                    ->select([
                        'country_id',
                        DB::raw('SUM(records.cost) AS `cost`'),
                        DB::raw('SUM(' . ($campaign->isCPC() ? 'TRUE' : 'FALSE') . ' != (records.cost!=0)) AS `impressions`'),
                        DB::raw('SUM(' . ($campaign->isCPC() ? 'TRUE' : 'FALSE') . ' = (records.cost!=0)) AS `clicks`')
                    ])
                    ->join('countries', function ($q) use ($tier) {
                        $q->whereColumn('countries.id', '=', 'records.country_id');
                        $q->where('countries.category', '=', $tier);
                    })
                    ->when($from, fn($query) => $query->where('records.time', '>=', $from))
                    ->when($to, fn($query) => $query->where('records.time', '<=', $to))
                    ->groupBy(['country_id', 'campaign_id'])
                    ->page($req->query->get('page'));

                $names = Country::whereIn('id', $countries->getCollection()->pluck('country_id')->toArray())->get(['id', 'name'])->groupBy('id');
                $rows = $countries->getCollection()->toString(function ($row) use ($names) {
                    $row->country = $names[$row->country_id][0]->name;
                    return $this->countriesListRow($row)->render();
                });

                return view('components.list.body', [
                    'url' => route('records.list', ['key' => 'countries', 'campaign' => $campaign->id, 'tier' => $tier], false),
                    'query' => json_encode($req->all(), JSON_FORCE_OBJECT),
                    'header' => ['Country', 'Cost', 'Impressions/Views', 'Clicks', 'CTR'],
                    'noAction' => true,
                    'rows' => $rows,
                    'pagination' => $countries->links()
                ]);
            }
            abort(403);
        }
        abort(404);
    }

    private function countriesListRow($row) {
        $data = [
            'id' => $row->country_id,
            'columns' => [
                $row->country,
                Helper::amount($row->cost),
                $row->impressions,
                $row->clicks,
                $row->impressions > 0 ? round($row->clicks / $row->impressions, 2) : 0
            ]
        ];
        return view('components.list.row', $data);
    }
}