<?php

namespace App\Http\Controllers\Records;

use App\Helpers\Helper;
use App\Models\Country;
use App\Models\Place;
use Auth;
use DB;
use Illuminate\Database\Query\JoinClause;

trait RecordsPlaceCountriesTrait {
    protected function placeCountriesIndex(Place|int $place, $tier) {
        $place = $place instanceof Place ? $place : Place::find($place);
        if (isset($place)) {
            return view('components.list.list', [
                'key' => 'ads',
                'header' => view('components.list.header', ['title' => self::TITLE['countries'] . ': ' . $tier]),
                'body' => $this->placeCountriesList($place, $tier)
            ]);
        }
        abort(404);
        return null;
    }

    protected function placeCountriesList(Place|int $place, $tier) {
        $place = $place instanceof Place ? $place : Place::find($place);
        if (isset($place)) {
            $user = Auth::user();
            if ($user->isManager() || $user->isPublisher() && $place->publisher()->user_id === $user->id) {
                $req = request();

                $from = $req->get('from');
                $to = $req->get('to');

                $countries = $place->records()
                    ->select([
                        'country_id',
                        DB::raw('SUM(records.cost * records.revenue) AS `earning`'),
                        DB::raw('SUM((ads_types.kind="CPC") != (records.cost!=0)) AS `impressions`'),
                        DB::raw('SUM((ads_types.kind="CPC") = (records.cost!=0)) AS `clicks`')
                    ])
                    ->join('places', function (JoinClause $join) {
                        $join->on('places.id', 'records.place_id');
                        $join->whereNull('places.deleted_at');
                    })
                    ->join('ads_types', 'ads_types.id', '=', 'places.ad_type_id')
                    ->join('countries', function ($q) use ($tier) {
                        $q->whereColumn('countries.id', '=', 'records.country_id');
                        $q->where('countries.category', '=', $tier);
                    })
                    ->when($from, fn($query) => $query->where('records.time', '>=', $from))
                    ->when($to, fn($query) => $query->where('records.time', '<=', $to))
                    ->groupBy(['country_id', 'place_id'])
                    ->page($req->query->get('page'));

                $names = Country::whereIn('id', $countries->getCollection()->pluck('country_id')->toArray())->get(['id', 'name'])->groupBy('id');
                $rows = $countries->getCollection()->toString(function ($row) use ($names) {
                    $row->country = $names[$row->country_id][0]->name;
                    return $this->placeCountriesListRow($row)->render();
                });

                return view('components.list.body', [
                    'url' => route('records.list', ['key' => 'placescountries', 'place' => $place->id, 'tier' => $tier], false),
                    'query' => json_encode($req->all(), JSON_FORCE_OBJECT),
                    'header' => ['Country', 'Earning', 'Impressions/Views', 'Clicks', 'CTR'],
                    'noAction' => true,
                    'rows' => $rows,
                    'pagination' => $countries->links()
                ]);
            }
            abort(403);
        }
        abort(404);
        return null;
    }

    private function placeCountriesListRow($row) {
        $data = [
            'id' => $row->country_id,
            'columns' => [
                $row->country,
                Helper::amount($row->earning),
                $row->impressions,
                $row->clicks,
                $row->impressions > 0 ? round($row->clicks / $row->impressions, 2) : 0
            ]
        ];
        return view('components.list.row', $data);
    }
}