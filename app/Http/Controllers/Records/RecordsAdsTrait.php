<?php

namespace App\Http\Controllers\Records;

use App\Helpers\Helper;
use App\Models\Ad;
use App\Models\AdType;
use App\Models\Category;
use App\Models\Record;
use App\Models\User;
use Auth;
use Carbon\Carbon;
use DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;

trait RecordsAdsTrait {
    use RecordsCampaignsTrait;

    protected function adsIndex() {
        $user = Auth::user();
        $isManager = $user->isManager();
        if (!$isManager && !$user->isAdvertiser()) {
            abort(404);
        }

        $result = '';

        foreach (['CPC', 'CPM', 'CPV'] as $category) {
            if ($user->isAdmin()) {
                $result .= view('components.list.list', [
                    'key' => 'ads_thirdparties',
                    'header' => view('components.list.header', [
                        'title' => $category . ' ' . self::TITLE['ads_thirdparties'],
                        'search' => $this->adsSearchForm('ads_thirdparties', $category)
                    ]),
                    'body' => $this->adsList(true, false, $category),
                    'charts' => $this->adsCharts(true, false, $category),
                ])->render();

                $result .= view('components.list.list', [
                    'key' => 'ads_admin',
                    'header' => view('components.list.header', [
                        'title' => $category . ' ' . self::TITLE['ads_admin'],
                        'search' => $this->adsSearchForm('ads_admin', $category)
                    ]),
                    'body' => $this->adsList(false, true, $category),
                    'charts' => $this->adsCharts(false, true, $category),
                ])->render();
            }

            $result .= view('components.list.list', [
                'key' => 'ads',
                'header' => view('components.list.header', [
                    'title' => $category . ' ' . self::TITLE['ads'],
                    'search' => $this->adsSearchForm('ads', $category)
                ]),
                'body' => $this->adsList(false, false, $category),
                'charts' => $this->adsCharts(false, false, $category),
            ])->render();
        }

        return $result;
    }

    private function adsSearchForm(string $key, string $category) {
        $filterOptions = [
            ['value' => 'overall', 'caption' => 'Overall'],
            ['value' => 'today', 'caption' => 'Today'],
            ['value' => 'last-week', 'caption' => 'Last Week'],
            ['value' => 'last-month', 'caption' => 'Last Month'],
            ['value' => 'last-year', 'caption' => 'Last Year'],
            ['value' => 'custom', 'caption' => 'Date Range']
        ];

        $filter_category_options = null;
        $filter_adtype_options = null;
        if (auth()->user()->isAdmin()) {
            $filter_category_options = Category::active()->get()->toString(fn($c) => Helper::option($c->id, $c->title));
            $filter_adtype_options = AdType::active()->get()->toString(fn($c) => Helper::option($c->id, $c->name));
        }

        return view('components.reports.search-form', [
            'key' => $key,
            'category' => $category,
            'options' => $filterOptions,
            'filter' => request()->get('filter', 'overall'),
            'from' => request()->get('from'),
            'to' => request()->get('to'),
            'filter_category_options' => $filter_category_options,
            'filter_category' => request()->get('filter_category'),
            'filter_adtype_options' => $filter_adtype_options,
            'filter_adtype' => request()->get('filter_adtype')
        ]);
    }

    protected function adsCharts(bool $thirdParties, bool $adminsAds, string $category): string {
        $req = request();

        $from = $to = null;
        $filterCategory = $filterAdType = null;
        if ($this->isSearch()) {
            $from = match ($req->get('filter')) {
                'today' => now()->startOfDay(),
                'last-week' => now()->subWeek()->startOfDay(),
                'last-month' => now()->subMonth()->startOfDay(),
                'last-year' => now()->subYear()->startOfDay(),
                'custom' => $req->get('from') !== null ? Carbon::parse($req->get('from'))->startOfDay() : null,
                default => null
            };

            $to = $req->get('filter') === 'custom' && $req->get('to') !== null ? Carbon::parse($req->get('to'))->endOfDay() : null;

            $filterCategory = auth()->user()->isAdmin() ? $req->get('filter_category') : null;
            $filterAdType = auth()->user()->isAdmin() ? $req->get('filter_adtype') : null;
        }

        $lineChartRecords = Record::query()
            ->select([
                DB::raw('DATE(records.time) AS `date`'),
                DB::raw('SUM(records.cost) AS `cost`'),
                DB::raw('SUM((ads_types.kind="CPC") != (records.cost!=0)) AS `impressions`'), // TODO: optimize query condition with $category when approved
                DB::raw('SUM((ads_types.kind="CPC")  = (records.cost!=0)) AS `clicks`') // TODO: optimize query condition with $category when approved
            ])
            ->join('campaigns', function ($query) use ($filterCategory) {
                $query->whereColumn('campaigns.id', 'records.campaign_id');
                $query->when($filterCategory, fn($query) => $query->where('campaigns.category_id', $filterCategory));
                $query->whereNull('campaigns.deleted_at');
            })
            ->join('ads', function ($j) use ($filterAdType, $adminsAds, $thirdParties) {
                $j->where('ads.is_third_party', $thirdParties);
                $j->whereColumn('ads.id', 'campaigns.ad_id');
                $j->whereNull('ads.deleted_at');
                $j->when($filterAdType, fn($query) => $query->where('ads.ad_type_id', $filterAdType));
                $j->when(!$thirdParties, function ($query) use ($adminsAds) {
                    if ($adminsAds) {
                        $query->whereNull('ads.advertiser_id');
                    } else {
                        $user = Auth::user();
                        $query->when(
                            !$user->isManager(),
                            fn($query) => $query->where('ads.advertiser_id', $user->id),
                            fn($query) => $query->whereNotNull('ads.advertiser_id')
                        );
                    }
                });
            })
            ->join('ads_types', function (JoinClause $query) use ($category) {
                $query->on('ads_types.id', 'ads.ad_type_id');
                $query->where('ads_types.kind', $category);
            })
            ->when($from, fn($query) => $query->where('records.time', '>=', $from->toDateTimeString()))
            ->when($to, fn($query) => $query->where('records.time', '<=', $to->toDateTimeString()))
            ->groupBy(DB::raw('DATE(records.time)'))
            ->orderBy('date')
            ->get();

        $pieChartRecords = Record::query()
            ->select([
                DB::raw('countries.category AS `tier`'),
                DB::raw('SUM(records.cost) AS `cost`'),
                DB::raw('SUM((ads_types.kind="CPC") != (records.cost!=0)) AS `impressions`'), // TODO: optimize query condition with $category when approved
                DB::raw('SUM((ads_types.kind="CPC")  = (records.cost!=0)) AS `clicks`') // TODO: optimize query condition with $category when approved
            ])
            ->join('campaigns', function ($query) use ($filterCategory) {
                $query->whereColumn('campaigns.id', 'records.campaign_id');
                $query->when($filterCategory, fn($query) => $query->where('campaigns.category_id', $filterCategory));
                $query->whereNull('campaigns.deleted_at');
            })
            ->join('ads', function ($j) use ($filterAdType, $adminsAds, $thirdParties) {
                $j->where('ads.is_third_party', $thirdParties);
                $j->whereColumn('ads.id', 'campaigns.ad_id');
                $j->whereNull('ads.deleted_at');
                $j->when($filterAdType, fn($query) => $query->where('ads.ad_type_id', $filterAdType));
                $j->when(!$thirdParties, function ($query) use ($adminsAds) {
                    if ($adminsAds) {
                        $query->whereNull('ads.advertiser_id');
                    } else {
                        $user = Auth::user();
                        $query->when(
                            !$user->isManager(),
                            fn($query) => $query->where('ads.advertiser_id', $user->id),
                            fn($query) => $query->whereNotNull('ads.advertiser_id')
                        );
                    }
                });
            })
            ->join('ads_types', function (JoinClause $query) use ($category) {
                $query->on('ads_types.id', 'ads.ad_type_id');
                $query->where('ads_types.kind', $category);
            })
            ->join('countries', 'countries.id', 'records.country_id')
            ->when($from, fn($query) => $query->where('records.time', '>=', $from->toDateTimeString()))
            ->when($to, fn($query) => $query->where('records.time', '<=', $to->toDateTimeString()))
            ->groupBy('tier')
            ->orderBy('tier')
            ->get();

        $showEmptyChart = $this->isSearch();

        $charts = '';
        if (!$lineChartRecords->isEmpty() || $showEmptyChart) {
            $charts .= $this->getRecordsChart(
                'overall-' . ($thirdParties ? 'third-parties' : ($adminsAds ? 'admin' : 'active')) . '-' . $category,
                $lineChartRecords,
                $category,
                3,
                'col-9'
            )->render();
        }
        if (!$pieChartRecords->isEmpty() || $showEmptyChart) {
            $charts .= $this->getRecordsCountriesPieChart(
                'overall-countries-' . ($thirdParties ? 'third-parties' : ($adminsAds ? 'admin' : 'active')) . '-' . $category,
                $pieChartRecords,
                $category,
                'col-3'
            )->render();
        }
        return $charts;
    }

    protected function adsList(bool $thirdParties, bool $adminsAds, ?string $category = null) {
        /** @var User $user */
        $user = Auth::user();
        $isManager = $user->isManager();
        $isAdmin = $user->isAdmin();

        if (!$isManager && !$user->isAdvertiser()) {
            abort(404);
        }

        if (($thirdParties || $adminsAds) && !$isAdmin) {
            abort(403);
        }

        $req = request();
        $category = $req->get('category', $category);

        $from = null;
        $to = null;
        $filterCategory = $filterAdType = null;
        if ($this->isSearch()) {
            $from = match ($req->get('filter')) {
                'today' => now()->startOfDay(),
                'last-week' => now()->subWeek()->startOfDay(),
                'last-month' => now()->subMonth()->startOfDay(),
                'last-year' => now()->subYear()->startOfDay(),
                'custom' => $req->get('from') !== null ? Carbon::parse($req->get('from'))->startOfDay() : null,
                default => null
            };

            $to = $req->get('filter') === 'custom' && $req->get('to') !== null ? Carbon::parse($req->get('to'))->endOfDay() : null;

            $filterCategory = auth()->user()->isAdmin() ? $req->get('filter_category') : null;
            $filterAdType = auth()->user()->isAdmin() ? $req->get('filter_adtype') : null;
        }

        $recClicks = 'IFNULL(records.clicks,0)';
        $recImpressions = 'IFNULL(records.impressions,0)';

        $ads = Ad::query()
            ->without((new Ad)->with)
            ->withoutTrashed()
            ->approved()
            ->where('ads.is_third_party', $thirdParties)
            ->when(!$thirdParties, function (Builder $query) use ($isManager, $user, $adminsAds) {
                if ($adminsAds) {
                    $query->whereNull('ads.advertiser_id');
                } else {
                    $query->when(
                        !$isManager,
                        fn(Builder $query) => $query->where('ads.advertiser_id', $user->id),
                        fn(Builder $query) => $query->whereNotNull('ads.advertiser_id')
                    );
                }
            })
            ->when($category, function (Builder $query) use ($category) {
                $query->join('ads_types', function (JoinClause $query) use ($category) {
                    $query->whereColumn('ads_types.id', 'ads.ad_type_id');
                    $query->where('ads_types.kind', $category);
                });
            })
            ->when($filterAdType, fn($query) => $query->where('ads.ad_type_id', $filterAdType))

            // Select
            ->select('ads.id')
            ->addSelect(DB::raw('SUM(IFNULL(invoices_campaigns.amount,0)) AS budget'))
            ->addSelect(DB::raw('SUM(IFNULL(invoices_campaigns.current,0)) AS cost'))
            ->addSelect(DB::raw("ROUND(SUM(IFNULL(records.cost,0)) / SUM(IFNULL(records.count,1)), 2) AS avg_cost"))
            ->addSelect(DB::raw("SUM($recClicks) AS clicks"))
            ->addSelect(DB::raw("SUM($recImpressions) AS impressions"))
            ->addSelect(DB::raw("SUM(IF($recImpressions != 0, $recClicks / $recImpressions, 0)) AS ctr"))
            ->join('campaigns', function (JoinClause $query) use ($filterCategory) {
                $query->on('campaigns.ad_id', 'ads.id');
                $query->where('campaigns.enabled', true);
                $query->whereNull('campaigns.stopped_at');
                $query->whereNull('campaigns.deleted_at');
                $query->when($filterCategory, fn($query) => $query->where('campaigns.category_id', $filterCategory));
            })

            // TODO: join with larger table!!!
            ->leftjoin('invoices_campaigns', 'invoices_campaigns.campaign_id', 'campaigns.id')

            // TODO: join with larger table!!!
            ->leftJoinSub(
                DB::table('records')
                    ->addSelect('campaign_id')
                    ->addSelect(DB::raw('COUNT(records.id) AS count'))
                    ->addSelect(DB::raw('SUM(cost) AS cost'))
                    ->when(
                        $category === 'CPC',
                        function ($query) {
                            $query->addSelect(DB::raw('SUM(cost != 0) AS clicks'));
                            $query->addSelect(DB::raw('SUM(cost  = 0) AS impressions'));
                        },
                        function ($query) {
                            $query->addSelect(DB::raw('SUM(cost = 0) AS clicks'));
                            $query->addSelect(DB::raw('SUM(cost != 0) AS impressions'));
                        }
                    )
                    ->when($from, fn($query) => $query->where('records.time', '>=', $from->toDateTimeString()))
                    ->when($to, fn($query) => $query->where('records.time', '<=', $to->toDateTimeString()))
                    ->groupBy('campaign_id'),
                'records',
                'records.campaign_id',
                'campaigns.id'
            )
            // Sorting
            ->when($req->query->get('sorting'),
                function ($q, $sorting) use ($req) {
                    $sorting = match ($sorting) {
                        'Budget' => 'budget',
                        'Impressions/Views' => 'impressions',
                        'Clicks' => 'clicks',
                        'CTR' => 'ctr',
                        'Avg. Cost' => 'avg_cost',
                    };
                    return $q->orderBy($sorting, $req->query->has('sorting_desc') ? 'desc' : 'asc');
                },
                fn($q) => $q->orderBy('ads.created_at', 'DESC')
            )
            ->groupBy('ads.id')
            ->page($req->query->get('page'));

        $key = $thirdParties ? 'ads_thirdparties' : ($adminsAds ? 'ads_admin' : 'ads');
        $query = json_encode(\Arr::add($req->all(), 'category', $category), JSON_FORCE_OBJECT | JSON_THROW_ON_ERROR);

        $items = $ads->getCollection()->keyBy('id')->toArray();
        $models = Ad::whereIn('id', array_keys($items))->get()->keyBy('id');
        $rows = array_reduce($items, function (string $carry, $item) use ($to, $from, $items, $models) {
            $ad = $models[$item['id']];

            $additionalData = [];

            foreach ($items[$ad->id] as $key => $value) {
                if ($key !== 'id') {
                    $additionalData[$key] = $value;
                }
            }

            $carry .= $this->adsListRow($ad, $additionalData)->render();
            return $carry;
        }, '');

        return view('components.list.body', [
            'url' => route('records.list', ['key' => $key], false),
            'query' => $query,
            'header' => ['Title', 'AD Type', 'Budget', 'Impressions/Views', 'Clicks', 'CTR', 'Avg. Cost', 'Actions'],
            'rows' => $rows,
            'pagination' => $ads->links(),
            'sorting' => [
                'columns' => ['Budget', 'Impressions/Views', 'Clicks', 'CTR', 'Avg. Cost'],
                'current' => $req->query->get('sorting'),
                'desc' => $req->query->has('sorting_desc')
            ],
        ]);
    }

    private function adsListRow(Ad $ad, array $additionalData) {
        $data = [
            'id' => $ad->id,
            'columns' => [
                $ad->getTitle(),
                $ad->adType->name . ' ' . $ad->adType->getSize(),
                Helper::amount($additionalData['budget'] - $additionalData['cost']),
                $additionalData['impressions'],
                $additionalData['clicks'],
                round($additionalData['ctr'], 2),
                'e' . $ad->adType->kind . ': ' . Helper::amount($additionalData['avg_cost'])
            ],
            'show' => [
                'url' => route('records.show-ad', ['ad' => $ad->id], false),
                'query' => json_encode([
                    'from' => request()->get('from'),
                    'to' => request()->get('to'),
                ], JSON_FORCE_OBJECT)
            ]
        ];
        return view('components.list.row', $data);
    }

    public function showAd(Ad $ad) {
        $user = Auth::user();
        $isManager = $user->isManager();
        if (!$isManager && $user->id != $ad->advertiser_id) {
            abort(403);
        }

        $from = request()->get('from');
        $to = request()->get('to');

        $lineChartRecords = $ad->records()
            ->select([
                DB::raw('DATE(records.time) AS `date`'),
                DB::raw('SUM(records.cost) AS `cost`'),
                DB::raw('SUM((' . ($ad->adType->isCPC() ? 'TRUE' : 'FALSE') . ') != (records.cost!=0)) AS `impressions`'),
                DB::raw('SUM((' . ($ad->adType->isCPC() ? 'TRUE' : 'FALSE') . ') = (records.cost!=0)) AS `clicks`')
            ])
            ->whereNull('campaigns.deleted_at')
            ->when($from, fn($query) => $query->where('records.time', '>=', $from))
            ->when($to, fn($query) => $query->where('records.time', '<=', $to))
            ->groupBy([DB::raw('DATE(records.time)'), 'campaigns.ad_id'])
            ->orderBy('date')
            ->get();

        $pieChartRecords = $ad->records()
            ->select([
                DB::raw('countries.category AS `tier`'),
                DB::raw('SUM(records.cost) AS `cost`'),
                DB::raw('SUM((' . ($ad->adType->isCPC() ? 'TRUE' : 'FALSE') . ') != (records.cost!=0)) AS `impressions`'),
                DB::raw('SUM((' . ($ad->adType->isCPC() ? 'TRUE' : 'FALSE') . ') = (records.cost!=0)) AS `clicks`')
            ])
            ->join('countries', 'countries.id', 'records.country_id')
            ->whereNull('campaigns.deleted_at')
            ->when($from, fn($query) => $query->where('records.time', '>=', $from))
            ->when($to, fn($query) => $query->where('records.time', '<=', $to))
            ->groupBy(['tier', 'campaigns.ad_id'])
            ->orderBy('tier')
            ->get();

        $charts = '';
        if (!$lineChartRecords->isEmpty()) {
            $charts .= $this->getRecordsChart($ad->id, $lineChartRecords, $ad->adType->kind, 3, 'col-9')->render();
        }
        if (!$pieChartRecords->isEmpty()) {
            $charts .= $this->getRecordsCountriesPieChart($ad->id . '-countries', $pieChartRecords, $ad->adType->kind, 'col-3')->render();
        }
        return view('components.list.row-details', [
            'rows' => [],
            'slot' => ($charts ? "<div class=\"row mx-0\">$charts</div>" : '') . $this->campaignsIndex($ad)
        ]);
    }

}