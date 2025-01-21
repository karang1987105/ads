<?php

namespace App\Http\Controllers\Records;

use App\Helpers\Helper;
use App\Models\AdType;
use App\Models\Category;
use App\Models\Place;
use App\Models\Record;
use Auth;
use Carbon\Carbon;
use DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Collection;

trait RecordsPlacesTrait {
    use RecordsPlaceCountriesTrait;

    protected function placesIndex() {
        $user = Auth::user();
        $isManager = $user->isManager();
        if (!$isManager && !$user->isPublisher()) {
            abort(404);
        }

        $result = '';

        foreach (['CPC', 'CPM', 'CPV'] as $category) {
            $result .= view('components.list.list', [
                'key' => 'places',
                'header' => view('components.list.header', [
                    'title' => $category . ' ' . self::TITLE['places'],
                    'search' => $this->placesSearchForm('places', $category)
                ]),
                'body' => $this->placesList($category),
                'charts' => $this->placesCharts($category)
            ])->render();
        }

        return $result;
    }

    private function placesSearchForm(string $key, string $category) {
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

    protected function placesList(string $category) {
        $user = Auth::user();
        $isManager = $user->isManager();
        if (!$isManager && !$user->isPublisher()) {
            abort(404);
        }

        $req = request();

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
        $recEarning = 'IFNULL(records.earning,0)';

        $places = ($isManager ? Place::query() : $user->publisher->places())
            ->without((new Place)->with)
            ->approved()
            ->when($category, function (Builder $query) use ($category) {
                $query->join('ads_types', function (JoinClause $query) use ($category) {
                    $query->on('ads_types.id', 'places.ad_type_id');
                    $query->where('ads_types.kind', $category);
                });
            })
            ->when($filterCategory, function (Builder $query) use ($filterCategory) {
                $query->join('users_publishers_domains', function (JoinClause $query) use ($filterCategory) {
                    $query->on('users_publishers_domains.id', 'places.domain_id');
                    $query->where('users_publishers_domains.category_id', $filterCategory);
                });
            })
            ->when($filterAdType, fn($query) => $query->where('places.ad_type_id', $filterAdType))

            // Select
            ->select('places.id')
            ->addSelect(DB::raw("SUM($recEarning) AS earning"))
            ->addSelect(DB::raw("ROUND(SUM($recEarning) / SUM(IFNULL(records.count,1)), 2) AS avg_earning"))
            ->addSelect(DB::raw("SUM($recClicks) AS clicks"))
            ->addSelect(DB::raw("SUM($recImpressions) AS impressions"))
            ->addSelect(DB::raw("SUM(IF($recImpressions != 0, $recClicks / $recImpressions, 0)) AS ctr"))

            // TODO: join with larger table!!!
            ->leftJoinSub(
                DB::table('records')
                    ->addSelect('place_id')
                    ->addSelect(DB::raw('COUNT(records.id) AS count'))
                    ->addSelect(DB::raw('SUM(cost * revenue) AS earning'))
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
                    ->when($from, fn($query) => $query->where('records.time', '>=', $from->startOfDay()->toDateTimeString()))
                    ->when($to, fn($query) => $query->where('records.time', '<=', $to->toDateTimeString()))
                    ->groupBy('place_id'),
                'records',
                'records.place_id',
                'places.id'
            )

            // Sorting
            ->when($req->query->get('sorting'),
                function ($q, $sorting) use ($req) {
                    $sorting = match ($sorting) {
                        'Earning' => 'earning',
                        'Impressions/Views' => 'impressions',
                        'Clicks' => 'clicks',
                        'CTR' => 'ctr',
                        'Avg. Earning' => 'avg_earning',
                    };
                    return $q->orderBy($sorting, $req->query->has('sorting_desc') ? 'desc' : 'asc');
                },
                fn($q) => $q->orderBy('places.created_at', 'DESC')
            )
            ->groupBy('places.id')
            ->page($req->query->get('page'));

        $query = json_encode(\Arr::add($req->all(), 'category', $category), JSON_FORCE_OBJECT | JSON_THROW_ON_ERROR);

        $items = $places->getCollection()->keyBy('id')->toArray();
        $models = Place::whereIn('id', array_keys($items))->get()->keyBy('id');
        $rows = array_reduce($items, function (string $carry, $item) use ($to, $from, $items, $models) {
            $ad = $models[$item['id']];

            $additionalData = [];

            foreach ($items[$ad->id] as $key => $value) {
                if ($key !== 'id') {
                    $additionalData[$key] = $value;
                }
            }

            $carry .= $this->placesListRow($ad, $additionalData)->render();
            return $carry;
        }, '');

        return view('components.list.body', [
            'url' => route('records.list', ['key' => 'places'], false),
            'query' => $query,
            'header' => ['Place', 'AD Type', 'Earning', 'Impressions/Views', 'Clicks', 'CTR', 'Avg. Earning', 'Actions'],
            'rows' => $rows,
            'pagination' => $places->links(),
            'sorting' => [
                'columns' => ['Earning', 'Impressions/Views', 'Clicks', 'CTR', 'Avg. Earning'],
                'current' => $req->query->get('sorting'),
                'desc' => $req->query->has('sorting_desc')
            ],
        ]);
    }

    protected function placesCharts(string $category): string {
        $user = Auth::user();
        $isManager = $user->isManager();
        if (!$isManager && !$user->isPublisher()) {
            abort(404);
        }

        $req = request();

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

        $lineChartRecords = Record::query()
            ->select([
                DB::raw('DATE(records.time) AS `date`'),
                DB::raw('SUM(records.cost * records.revenue) AS `earning`'),
                DB::raw('SUM((ads_types.kind="CPC") != (records.cost!=0)) AS `impressions`'),
                DB::raw('SUM((ads_types.kind="CPC") = (records.cost!=0)) AS `clicks`')
            ])
            ->join('places', function (JoinClause $join) {
                $join->on('places.id', 'records.place_id');
                $join->whereNull('places.deleted_at');
            })
            ->join('users_publishers_domains', function ($j) use ($filterCategory, $isManager) {
                $j->whereColumn('users_publishers_domains.id', '=', 'places.domain_id');
                $j->whereNotNull('users_publishers_domains.approved_at');
                if (!$isManager) {
                    $j->where('users_publishers_domains.publisher_id', '=', Auth::id());
                }
                $j->when($filterCategory, fn($j) => $j->where('users_publishers_domains.category_id', $filterCategory));
            })
            ->join('ads_types', function (JoinClause $query) use ($filterAdType, $category) {
                $query->on('ads_types.id', 'places.ad_type_id');
                $query->where('ads_types.kind', $category);
            })
            ->when($from, fn($query) => $query->where('records.time', '>=', $from->toDateTimeString()))
            ->when($to, fn($query) => $query->where('records.time', '<=', $to->toDateTimeString()))
            ->when($filterAdType, fn($query) => $query->where('places.ad_type_id', $filterAdType))
            ->groupBy(DB::raw('DATE(records.time)'))
            ->orderBy('date')
            ->get();

        $pieChartRecords = Record::query()
            ->select([
                DB::raw('countries.category AS `tier`'),
                DB::raw('SUM(records.cost * records.revenue) AS `earning`'),
                DB::raw('SUM((ads_types.kind="CPC") != (records.cost!=0)) AS `impressions`'),
                DB::raw('SUM((ads_types.kind="CPC") = (records.cost!=0)) AS `clicks`')
            ])
            ->join('countries', 'countries.id', '=', 'records.country_id')
            ->join('places', function (JoinClause $join) {
                $join->on('places.id', 'records.place_id');
                $join->whereNull('places.deleted_at');
            })
            ->join('users_publishers_domains', function ($j) use ($filterCategory, $isManager) {
                $j->whereColumn('users_publishers_domains.id', '=', 'places.domain_id');
                $j->whereNotNull('users_publishers_domains.approved_at');
                if (!$isManager) {
                    $j->where('users_publishers_domains.publisher_id', '=', Auth::id());
                }
                $j->when($filterCategory, fn($j) => $j->where('users_publishers_domains.category_id', $filterCategory));
            })
            ->join('ads_types', function (JoinClause $query) use ($category) {
                $query->on('ads_types.id', 'places.ad_type_id');
                $query->where('ads_types.kind', $category);
            })
            ->when($from, fn($query) => $query->where('records.time', '>=', $from->toDateTimeString()))
            ->when($to, fn($query) => $query->where('records.time', '<=', $to->toDateTimeString()))
            ->when($filterAdType, fn($query) => $query->where('places.ad_type_id', $filterAdType))
            ->groupBy('tier')
            ->orderBy('tier')
            ->get();

        $charts = '';

        $showEmptyChart = $this->isSearch();

        if (!$lineChartRecords->isEmpty() || $showEmptyChart) {
            $charts .= $this->getRecordsPlaceChart('places-overall-'.$category, $lineChartRecords, $category, 3, 'col-9')->render();
        }

        if (!$pieChartRecords->isEmpty() || $showEmptyChart) {
            $charts .= $this->getRecordsPlaceCountriesPieChart('places-overall-countries-'.$category, $pieChartRecords, $category, 'col-3')->render();
        }

        return $charts;
    }

    private function placesListRow(Place $place, array $additionalData) {
        $data = [
            'id' => $place->id,
            'columns' => [
                $place->title,
                $place->adType->name . ' ' . $place->adType->getSize(),
                Helper::amount($additionalData['earning']),
                $additionalData['impressions'],
                $additionalData['clicks'],
                round($additionalData['ctr'], 2),
                'e' . $place->adType->kind . ': ' . Helper::amount($additionalData['avg_earning'])
            ],
            'show' => [
                'url' => route('records.show-place', ['place' => $place->id], false),
                'query' => json_encode([
                    'from' => request()->get('from'),
                    'to' => request()->get('to'),
                ], JSON_FORCE_OBJECT)
            ]
        ];
        return view('components.list.row', $data);
    }

    public function showPlace(Place $place) {
        $user = Auth::user();
        $isManager = $user->isManager();
        if (!$isManager && $user->id != $place->publisher()->user_id) {
            abort(403);
        }

        $from = request()->get('from');
        $to = request()->get('to');

        $lineChartRecords = $place->records()
            ->select([
                DB::raw('DATE(records.time) AS `date`'),
                DB::raw('SUM(records.cost * records.revenue) AS `earning`'),
                DB::raw('SUM((' . ($place->adType->isCPC() ? 'TRUE' : 'FALSE') . ') != (records.cost!=0)) AS `impressions`'),
                DB::raw('SUM((' . ($place->adType->isCPC() ? 'TRUE' : 'FALSE') . ') = (records.cost!=0)) AS `clicks`')
            ])
            ->when($from, fn($query) => $query->where('records.time', '>=', $from))
            ->when($to, fn($query) => $query->where('records.time', '<=', $to))
            ->groupBy(DB::raw('DATE(records.time)'))
            ->orderBy('date')
            ->get();

        $pieChartRecords = $place->records()
            ->select([
                DB::raw('countries.category AS `tier`'),
                DB::raw('SUM(records.cost * records.revenue) AS `earning`'),
                DB::raw('SUM((' . ($place->adType->isCPC() ? 'TRUE' : 'FALSE') . ') != (records.cost!=0)) AS `impressions`'),
                DB::raw('SUM((' . ($place->adType->isCPC() ? 'TRUE' : 'FALSE') . ') = (records.cost!=0)) AS `clicks`')
            ])
            ->join('countries', 'countries.id', '=', 'records.country_id')
            ->when($from, fn($query) => $query->where('records.time', '>=', $from))
            ->when($to, fn($query) => $query->where('records.time', '<=', $to))
            ->groupBy('tier')
            ->orderBy('tier')
            ->get();

        $charts = '';
        if (!$lineChartRecords->isEmpty()) {
            $charts .= $this->getRecordsPlaceChart('places-' . $place->id, $lineChartRecords, $place->adType->kind, 3, 'col-9')->render();
        }
        if (!$pieChartRecords->isEmpty()) {
            $charts .= $this->getRecordsPlaceCountriesPieChart('places-' . $place->id . '-countries', $pieChartRecords, $place->adType->kind, 'col-3')->render();
        }
        return view('components.list.row-details', [
            'rows' => [],
            'slot' => ($charts ? "<div class=\"row mx-0\">$charts</div>" : '') .
                $this->placeCountriesIndex($place, 'Tier 1') .
                $this->placeCountriesIndex($place, 'Tier 2') .
                $this->placeCountriesIndex($place, 'Tier 3') .
                $this->placeCountriesIndex($place, 'Tier 4')
        ]);
    }

    protected function getRecordsPlaceChart($key, Collection $records, string $category, $aspectRatio, $classes = null) {
        // $color = Helper::getRandomColors(6);
        $color = [
            'rgb(31, 119, 180)', // Blue
            'rgb(255, 127, 14)', // Orange
            'rgb(44, 160, 44)', // Green
            'rgb(214, 39, 40)', // Red
            'rgb(148, 103, 189)', // Purple
            'rgb(23, 190, 207)'  // Cyan
        ];
        $opacity = 0.5; // 50% opacity

        $index = -1;
        $getDataset = function ($title) use ($color, &$index) {
            $index += 1;
            // Convert RGB to RGBA by appending the opacity
            $colorWithOpacity = str_replace('rgb', 'rgba', $color[$index]);
            $colorWithOpacity = str_replace(')', ", 0.3)", $colorWithOpacity);
            return [
                'label' => $title,
                'backgroundColor' => $colorWithOpacity,
                'pointBackgroundColor' => $color[$index],
                'borderColor' => $color[$index],
                'borderWidth' => 2,
                'tension' => 0.3,
                'yAxisID' => $index < 2 ? 'y1' : 'y',
                'data' => [],
                'pointRadius'=> 6,
                'fill' => true
            ];
        };
        $datasets = [
            'total_earning' => $getDataset('Total Earning'),
            'earning' => $getDataset('Earning'),
            'impressions' => $getDataset($category === 'CPC' ? 'Impressions/Views' : ($category === 'CPV' ? 'Views' : 'Impressions')),
            'clicks' => $getDataset('Clicks'),
            'ctr' => $getDataset('CTR'),
        ];
        $total_cost = 0;
        foreach ($records as $record) {
            $label = Carbon::parse($record->date)->format('F d');
            $total_cost += $record->earning;
            // $datasets['total_earning']['data'][] = ['x' => $label, 'y' => $record->earning + array_sum(array_column($datasets['total_earning']['data'], 'y'))];
            $datasets['total_earning']['data'][] = ['x' => $label, 'y' => $total_cost];
            $datasets['earning']['data'][] = ['x' => $label, 'y' => $record->earning];
            $datasets['impressions']['data'][] = ['x' => $label, 'y' => $record->impressions];
            $datasets['clicks']['data'][] = ['x' => $label, 'y' => $record->clicks];
            $datasets['ctr']['data'][] = ['x' => $label, 'y' => $record->impressions != 0 ? round($record->clicks / $record->impressions, 2) : 0];
        }
        $datasets = json_encode(array_values($datasets));
        $options = json_encode([
            'aspectRatio' => $aspectRatio,
            'scales' => [
                'y' => [
                    'type' => 'linear',
                    'display' => true,
                    'position' => 'left'
                ],
                'y1' => [
                    'type' => 'logarithmic',
                    'display' => true,
                    'position' => 'right',
                    'grid' => ['display' => false],
                    'ticks' => ['callback' => '#!!(value, index, values) => \'$\'+value!!#']
                ],
            ],
            'plugins' => [
                'tooltip' => [
                    'usePointStyle' => true,
                    'callbacks' => [
                        'label' => "#!!(context) => (context.dataset.label||'')+': '+(context.datasetIndex<2?'$':'')+context.formattedValue!!#",
                        'labelPointStyle' => "#!!(context) => ({pointStyle: 'circle'})!!#",
                    ]
                ],
                'legend' => [
                    'labels' => [
                        'color' => 'white',
                        'usePointStyle' => true, // Use circular legend markers
                        'pointStyle' => 'circle', // Set the point style to circle
                    ]
                ]
            ]
        ]);
        $options = str_replace(['"#!!', '!!#"'], '', $options);

        return view('components.chart', [
            'id' => 'chart_ad_' . $key,
            'type' => 'line',
            'datasets' => $datasets,
            'options' => $options,
            'classes' => $classes,
            'update' => $this->isSearch()
        ]);
    }

    protected function getRecordsPlaceCountriesPieChart($key, Collection $records, string $category, $classes = null) {
        $backgroundColor = [
            'rgb(255, 127, 14)', // Orange
            'rgb(44, 160, 44)', // Green
            'rgb(214, 39, 40)', // Red
            'rgb(148, 103, 189)', // Purple
        ];
        $datasets = [
            'Earning' => ['backgroundColor' => $backgroundColor[0], 'data' => []],
            'Impressions' => ['backgroundColor' => $backgroundColor[1], 'data' => []],
            'Clicks' => ['backgroundColor' => $backgroundColor[2], 'data' => []],
            'CTR' => ['backgroundColor' => $backgroundColor[3], 'data' => []],
        ];
        $labels = ['Earning' => [], 'Impressions' => [], 'Clicks' => [], 'CTR' => []];

        for ($t = 0; $t < 4; $t += 1) {
            $labels['Earning'][] = 'Tier ' . ($t + 1) . ' earning';
            $labels['Impressions'][] = 'Tier ' . ($t + 1) . ' ' . ($category === 'CPC' ? 'Impressions/Views' : ($category === 'CPV' ? 'Views' : 'Impressions'));
            $labels['Clicks'][] = 'Tier ' . ($t + 1) . ' clicks';
            $labels['CTR'][] = 'Tier ' . ($t + 1) . ' CTR';

            $record = $records->where('tier', 'Tier ' . ($t + 1))->first();
            if ($record) {
                $datasets['Earning']['data'][] = $record->earning;
                $datasets['Earning']['borderColor'] = '#FFFFFF';
                $datasets['Earning']['borderWidth'] = 2;
                $datasets['Earning']['borderRadius'] = 15;

                $datasets['Impressions']['data'][] = $record->impressions;
                $datasets['Impressions']['borderColor'] = '#FFFFFF';
                $datasets['Impressions']['borderWidth'] = 2;
                $datasets['Impressions']['borderRadius'] = 15;

                $datasets['Clicks']['data'][] = $record->clicks;
                $datasets['Clicks']['borderColor'] = '#FFFFFF';
                $datasets['Clicks']['borderWidth'] = 2;
                $datasets['Clicks']['borderRadius'] = 15;

                $datasets['CTR']['data'][] = $record->impressions > 0 ? $record->clicks / $record->impressions : 0;
                $datasets['CTR']['borderColor'] = '#FFFFFF';
                $datasets['CTR']['borderWidth'] = 2;
                $datasets['CTR']['borderRadius'] = 15;
                
            } else {
                $datasets['Earning']['data'][] = 0;
                $datasets['Impressions']['data'][] = 0;
                $datasets['Clicks']['data'][] = 0;
                $datasets['CTR']['data'][] = 0;
            }

        }
//        foreach ($datasets as $k => $v) {
//            if (array_sum($v['data']) == 0) {
//                unset($datasets[$k], $labels[$k]);
//            }
//        }
        $labels = array_merge(...array_values($labels));
        $datasets = array_values($datasets);
        $options = json_encode([
            'plugins' => [
                'legend' => ['display' => false],
                'tooltip' => [
                    'usePointStyle' => true,
                    'callbacks' => [
                        'label' => "#!!(context) => context.chart.data.labels[(context.datasetIndex*4)+context.dataIndex]+': '+context.formattedValue!!#",
                        'labelPointStyle' => "#!!(context) => ({pointStyle: 'circle'})!!#"
                    ]
                ]
            ],
            'cutout' => '50%'
        ]);
        $options = str_replace(['"#!!', '!!#"'], '', $options);

        return view('components.chart', [
            'id' => 'chart_ad_' . $key,
            'type' => 'pie',
            'data' => json_encode([
                'labels' => $labels,
                'datasets' => $datasets
            ]),
            'options' => $options,
            'classes' => $classes,
            'update' => $this->isSearch()
        ]);
    }
}