<?php

namespace App\Http\Controllers\Records;

use App\Helpers\Helper;
use App\Models\Ad;
use App\Models\Campaign;
use App\Models\InvoiceCampaign;
use Auth;
use Carbon\Carbon;
use DB;
use Illuminate\Support\Collection;
use stdClass;

trait RecordsCampaignsTrait {
    use RecordsCampaignCountriesTrait;

    protected function campaignsIndex(Ad|int $ad) {
        $ad = $ad instanceof Ad ? $ad : Ad::withoutTrashed()->find($ad);
        if (isset($ad) && $ad->deleted_at === null) {
            return view('components.list.list', [
                'key' => 'ads',
                'header' => view('components.list.header', ['title' => self::TITLE['ads']]),
                'body' => $this->campaignsList($ad)
            ]);
        }
        abort(404);
    }

    protected function campaignsList(Ad|int $ad) {
        $ad = $ad instanceof Ad ? $ad : Ad::withoutTrashed()->find($ad);
        if (isset($ad) && $ad->deleted_at === null) {
            $user = Auth::user();
            if ($user->isManager() || ($user->isAdvertiser() && $ad->advertiser_id === $user->id)) {
                $req = request();

                $campaigns = $ad->campaigns()
                    ->withoutTrashed()
                    ->active()
                    ->enabled()
                    ->orderByDesc('created_at')
                    ->page($req->query->get('page'));

                return view('components.list.body', [
                    'url' => route('records.list', ['key' => 'campaigns', 'ad' => $ad->id], false),
                    'query' => json_encode($req->all(), JSON_FORCE_OBJECT),
                    'header' => ['Budget', 'Impressions/Views', 'Clicks', 'CTR', 'Avg. Cost', 'Actions'],
                    'rows' => $campaigns->getCollection()->toString(fn($campaign) => $this->campaignsListRow($campaign, $ad->adType->kind)->render()),
                    'pagination' => $campaigns->links()
                ]);
            }
            abort(403);
        }
        abort(404);
    }

    private function campaignsListRow(Campaign $campaign, string $adTypeKind) {
        $campaign->load(['ad' => fn($q) => $q->withoutTrashed()]);

        $stats = $this->getCampaignStats($campaign);
        $data = [
            'id' => $campaign->id,
            'columns' => [
                Helper::amount($stats->budget - $stats->cost),
                $stats->impressions,
                $stats->clicks,
                $stats->impressions > 0 ? round($stats->clicks / $stats->impressions, 2) : 0,
                'e' . $adTypeKind . ': ' . Helper::amount($stats->recordsAvgCost)
            ],
            'show' => [
                'url' => route('records.show-campaign', ['campaign' => $campaign->id], false),
                'query' => json_encode([
                    'from' => request()->get('from'),
                    'to' => request()->get('to'),
                ], JSON_FORCE_OBJECT)
            ]
        ];
        return view('components.list.row', $data);
    }

    public function showCampaign(Campaign $campaign) {
        $campaign->load(['ad' => fn($q) => $q->withoutTrashed()]);

        $user = Auth::user();
        if (!$user->isManager() && $user->id != $campaign->ad->advertiser_id) {
            abort(403);
        }

        $from = request()->get('from');
        $to = request()->get('to');

        $lineChartRecords = $campaign->records()
            ->select([
                DB::raw('DATE(records.time) AS `date`'),
                DB::raw('SUM(records.cost) AS `cost`'),
                DB::raw('SUM(' . ($campaign->isCPC() ? 'TRUE' : 'FALSE') . ' != (records.cost!=0)) AS `impressions`'),
                DB::raw('SUM(' . ($campaign->isCPC() ? 'TRUE' : 'FALSE') . ' = (records.cost!=0)) AS `clicks`')
            ])
            ->when($from, fn($query) => $query->where('records.time', '>=', $from))
            ->when($to, fn($query) => $query->where('records.time', '<=', $to))
            ->groupBy([DB::raw('DATE(records.time)'), 'campaign_id'])
            ->orderBy('date')
            ->get();

        $pieChartRecords = $campaign->records()
            ->select([
                DB::raw('countries.category AS `tier`'),
                DB::raw('SUM(records.cost) AS `cost`'),
                DB::raw('SUM(' . ($campaign->isCPC() ? 'TRUE' : 'FALSE') . ' != (records.cost!=0)) AS `impressions`'),
                DB::raw('SUM(' . ($campaign->isCPC() ? 'TRUE' : 'FALSE') . ' = (records.cost!=0)) AS `clicks`')
            ])
            ->join('countries', 'countries.id', '=', 'records.country_id')
            ->when($from, fn($query) => $query->where('records.time', '>=', $from))
            ->when($to, fn($query) => $query->where('records.time', '<=', $to))
            ->groupBy(['tier', 'campaign_id'])
            ->orderBy('tier')
            ->get();

        $charts = '';
        if (!$lineChartRecords->isEmpty()) {
            $charts .= $this->getRecordsChart('camp_' . $campaign->id, $lineChartRecords, $campaign->getAdKind(), 3, 'col-9')->render();
        }
        if (!$pieChartRecords->isEmpty()) {
            $charts .= $this->getRecordsCountriesPieChart('camp_' . $campaign->id . '-countries', $pieChartRecords, $campaign->getAdKind(), 'col-3')->render();
        }
        return view('components.list.row-details', [
            'rows' => [],
            'slot' => ($charts ? "<div class=\"row mx-0\">$charts</div>" : '') .
                $this->countriesIndex($campaign, 'Tier 1') .
                $this->countriesIndex($campaign, 'Tier 2') .
                $this->countriesIndex($campaign, 'Tier 3') .
                $this->countriesIndex($campaign, 'Tier 4')
        ]);
    }

    protected function getCampaignStats(Campaign $campaign): stdClass {
        $data = new stdClass();
        $data->budget = 0.0;
        $data->cost = 0.0;
        $data->recordsCost = 0.0;
        $data->recordsAvgCost = 0.0;
        $data->impressions = 0;
        $data->clicks = 0;

        $campaign->invoices()->withoutTrashed()->get()->each(function (InvoiceCampaign $ic) use (&$data) {
            $data->budget += (float)$ic->amount;
            $data->cost += (float)$ic->current;
        });

        $from = request()->get('from');
        $to = request()->get('to');

        $records = $campaign->records()
            ->when($from, fn($query) => $query->where('records.time', '>=', $from))
            ->when($to, fn($query) => $query->where('records.time', '<=', $to))
            ->get();

        $costRecordCount = 0;
        for ($i = 0, $count = $records->count(); $i < $count; $i += 1) {
            $record = $records[$i];
            $clicked = $campaign->isCPC() ? $record->cost != 0 : $record->cost == 0;
            $data->clicks += $clicked ? 1 : 0;
            $data->impressions += !$clicked ? 1 : 0;
            $data->recordsCost += (float)$record->cost;
            $costRecordCount += $record->cost != 0 ? 1 : 0;
        }
        $data->recordsAvgCost = $costRecordCount > 0 ? $data->recordsCost / $costRecordCount : 0;

        return $data;
    }

    protected function getRecordsChart($key, Collection $records, string $category, $aspectRatio, $classes = null) {
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
                'pointRadius' => 6,
                'fill' => true
            ];
        };
        $datasets = [
            'total_cost' => $getDataset('Total Cost'),
            'cost' => $getDataset('Cost'),
            'impressions' => $getDataset($category === 'CPC' ? 'Impressions/Views' : ($category === 'CPV' ? 'Views' : 'Impressions')),
            'clicks' => $getDataset('Clicks'),
            'ctr' => $getDataset('CTR'),
            // 'pointRadius': 10,
        ];
        $total_cost = 0;
        foreach ($records as $record) {
            $total_cost += $record->cost;
            $label = Carbon::parse($record->date)->format('F d');
            // $datasets['total_cost']['data'][] = ['x' => $label, 'y' => $record->cost + array_sum(array_column($datasets['total_cost']['data'], 'y'))];
            $datasets['total_cost']['data'][] = ['x' => $label, 'y' => $total_cost];
            $datasets['cost']['data'][] = ['x' => $label, 'y' => $record->cost];
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
                        'labelPointStyle' => "#!!(context) => ({pointStyle: 'circle'})!!#"
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

    protected function getRecordsCountriesPieChart($key, Collection $records, string $category, $classes = null) {
        $color = Helper::getRandomColors(4);
        // $backgroundColor = [$color[0], $color[1], $color[2], $color[3]];
        $backgroundColor = [
            'rgb(255, 127, 14)', // Orange
            'rgb(44, 160, 44)', // Green
            'rgb(214, 39, 40)', // Red
            'rgb(148, 103, 189)', // Purple
        ];
        $datasets = [
            'Cost' => ['backgroundColor' => $backgroundColor[0], 'data' => []],
            'Impressions' => ['backgroundColor' => $backgroundColor[1], 'data' => []],
            'Clicks' => ['backgroundColor' => $backgroundColor[2], 'data' => []],
            'CTR' => ['backgroundColor' => $backgroundColor[3], 'data' => []],
        ];
        $labels = ['Cost' => [], 'Impressions' => [], 'Clicks' => [], 'CTR' => []];

        for ($t = 0; $t < 4; $t += 1) {
            $labels['Cost'][] = 'Tier ' . ($t + 1) . ' cost';
            $labels['Impressions'][] = 'Tier ' . ($t + 1) . ' ' . ($category === 'CPC' ? 'Impressions/Views' : ($category === 'CPV' ? 'Views' : 'Impressions'));
            $labels['Clicks'][] = 'Tier ' . ($t + 1) . ' clicks';
            $labels['CTR'][] = 'Tier ' . ($t + 1) . ' CTR';

            $record = $records->where('tier', 'Tier ' . ($t + 1))->first();
            if ($record) {
                $datasets['Cost']['data'][] = $record->cost;
                $datasets['Cost']['borderColor'] = '#FFFFFF';
                $datasets['Cost']['borderWidth'] = 2;
                $datasets['Cost']['borderRadius'] = 15;

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
                $datasets['Cost']['data'][] = 0;
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