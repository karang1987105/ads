<?php

namespace App\Http\Controllers\Records;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\InvoiceCampaign;
use App\Models\Record;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;

class RecordsController extends Controller {
    use RecordsAdsTrait, RecordsPlacesTrait;

    private const TITLE = [
        'ads' => 'Active ADS',
        'ads_thirdparties' => 'Active Third Party ADS',
        'ads_admin' => 'Admin ADS',
        'campaigns' => 'Active Campaigns',
        'countries' => 'Countries',
        'places' => 'Places'
    ];

    private array $cache = [
        'card_stats' => [],
        'paid_stats' => [],
    ];

    public function index($key) {
        return match ($key) {
            'ads' => $this->adsIndex(),
            'campaigns' => $this->campaignsIndex(request()->get('ad')),
            'countries' => $this->countriesIndex(request()->get('campaign'), request()->get('tier')),
            'places' => $this->placesIndex(),
        };
    }

    public function list($key) {
        $category = request()->get('category');
    
        $data = match ($key) {
            'ads' => [
                'list' => $this->adsList(false, false, $category),
                'charts' => $this->adsCharts(false, false, $category),
            ],
            'ads_thirdparties' => [
                'list' => $this->adsList(true, false),
                'charts' => $this->adsCharts(true, false, $category),
            ],
            'ads_admin' => [
                'list' => $this->adsList(false, true),
                'charts' => $this->adsCharts(false, true, $category),
            ],
            'campaigns' => $this->campaignsList(request()->get('ad')),
            'countries' => $this->countriesList(request()->get('campaign'), request()->get('tier')),
            'places' => [
                'list' => $this->placesList($category),
                'charts' => $this->placesCharts($category),
            ],
            'placescountries' => $this->placeCountriesList(request()->get('place'), request()->get('tier')),
            default => [],
        };
    
        return view('records.list', compact('data', 'key', 'category'));
    }

    public function getTotalImpressionsCardData(): array {
        $this->cacheTotalStatsCardData();

        [$olderValue, $yesterdayValue, $todayValue] = $this->cache['card_stats']['impressions'];

        $todayChange = !empty($yesterdayValue) ? round(($todayValue - $yesterdayValue) / $yesterdayValue * 100, 2) : null;

        $stats = [
            [
                'value' => 'Yesterday ' . number_format($yesterdayValue)
            ],
            [
                'value' => 'Today ' . number_format($todayValue)
            ]
        ];

        if (isset($todayChange)) {
            $stats[] = [
                'value' => abs($todayChange) . '%',
                'increase' => $todayChange > 0
            ];
        }
        return [
            'title' => 'Total Impressions / Views',
            'value' => number_format($olderValue + $yesterdayValue + $todayValue),
            'stats' => $stats
        ];
    }

    public function getTotalClicksCardData(): array {
        $this->cacheTotalStatsCardData();

        [$olderValue, $yesterdayValue, $todayValue] = $this->cache['card_stats']['clicks'];

        $todayChange = !empty($yesterdayValue) ? round(($todayValue - $yesterdayValue) / $yesterdayValue * 100, 2) : null;

        $stats = [
            [
                'value' => 'Yesterday ' . number_format($yesterdayValue)
            ],
            [
                'value' => 'Today ' . number_format($todayValue)
            ]
        ];

        if (isset($todayChange)) {
            $stats[] = [
                'value' => abs($todayChange) . '%',
                'increase' => $todayChange > 0
            ];
        }
        return [
            'title' => 'Total Clicks',
            'value' => number_format($olderValue + $yesterdayValue + $todayValue),
            'stats' => $stats
        ];
    }

    public function getTotalCTRCardData(): array {
        $this->cacheTotalStatsCardData();

        [$olderClicksValue, $yesterdayClicksValue, $todayClicksValue] = $this->cache['card_stats']['clicks'];
        [$olderImpressionsValue, $yesterdayImpressionsValue, $todayImpressionsValue] = $this->cache['card_stats']['impressions'];

        $olderValue = !empty($olderImpressionsValue) ? round($olderClicksValue / $olderImpressionsValue, 2) : 0;
        $yesterdayValue = !empty($yesterdayImpressionsValue) ? round($yesterdayClicksValue / $yesterdayImpressionsValue, 2) : 0;
        $todayValue = !empty($todayImpressionsValue) ? round($todayClicksValue / $todayImpressionsValue, 2) : 0;

        $todayChange = !empty($yesterdayValue) ? round(($todayValue - $yesterdayValue) / $yesterdayValue, 2) : null;

        $stats = [
            [
                'value' => 'Yesterday ' . number_format($yesterdayValue, 2)
            ],
            [
                'value' => 'Today ' . number_format($todayValue, 2)
            ]
        ];

        if (isset($todayChange)) {
            $stats[] = [
                'value' => abs($todayChange * 100) . '%',
                'increase' => $todayChange > 0
            ];
        }
        return [
            'title' => 'Total CTR',
            'value' => number_format($olderValue + $yesterdayValue + $todayValue, 2),
            'stats' => $stats
        ];
    }

    public function getTotalDataWithEachCountries(): array {
        $this->cacheTotalStatsCardData();
        return $this->cache['card_stats']['CPCDataEachCountries'];
    }

    private function cacheTotalStatsCardData(): void {
        if (empty($this->cache['card_stats'])) {
            $user = auth()->user();

            $managerAdTypesFn = function (Builder $query) {
                $query->join('campaigns', 'campaigns.id', 'records.campaign_id');
                $query->join('ads', 'ads.id', 'campaigns.ad_id');
                $query->join('ads_types', 'ads_types.id', 'ads.ad_type_id');
            };

            $advertiserAdTypeFn = function (Builder $query) use ($user) {
                $query->join('campaigns', 'campaigns.id', 'records.campaign_id');
                $query->join('ads', 'ads.id', 'campaigns.ad_id');
                $query->where('ads.advertiser_id', $user->id);
                $query->join('ads_types', 'ads_types.id', 'ads.ad_type_id');
            };

            $publisherAdTypeFn = function (Builder $query) use ($user) {
                $query->join('places', 'places.id', 'records.place_id');
                $query->join('users_publishers_domains', 'users_publishers_domains.id', 'places.domain_id');
                $query->where('users_publishers_domains.publisher_id', $user->id);
                $query->join('ads_types', 'ads_types.id', 'places.ad_type_id');
            };

            $oldValues = Record::query()
                ->selectRaw("country_id, SUM(IF(ads_types.kind='CPC', records.cost != 0, records.cost = 0)) as clicks")
                ->selectRaw("SUM(IF(ads_types.kind='CPC', records.cost = 0, records.cost != 0)) as impressions")
                ->when($user->isManager(), $managerAdTypesFn)
                ->when($user->isAdvertiser(), $advertiserAdTypeFn)
                ->when($user->isPublisher(), $publisherAdTypeFn)
               
                ->groupBy('country_id')
                ->get();
            
            $oldValuesEachCountries = [];
            foreach($oldValues as $oldValue) {
                $oldValuesEachCountries[] = [
                    'country_id' => $oldValue->country_id,
                    'clicks' => $oldValue->clicks,
                    'impressions' => $oldValue->impressions
                ];
            }

            $oldValues = Record::query()
                ->selectRaw("SUM(IF(ads_types.kind='CPC', records.cost != 0, records.cost = 0)) as clicks")
                ->selectRaw("SUM(IF(ads_types.kind='CPC', records.cost = 0, records.cost != 0)) as impressions")
                ->when($user->isManager(), $managerAdTypesFn)
                ->when($user->isAdvertiser(), $advertiserAdTypeFn)
                ->when($user->isPublisher(), $publisherAdTypeFn)
                ->where('records.time', '<', now()->yesterday()->startOfDay())
                ->first();

            $yesterdayValues = Record::query()
                ->selectRaw("SUM(IF(ads_types.kind='CPC', records.cost != 0, records.cost = 0)) as clicks")
                ->selectRaw("SUM(IF(ads_types.kind='CPC', records.cost = 0, records.cost != 0)) as impressions")
                ->when($user->isManager(), $managerAdTypesFn)
                ->when($user->isAdvertiser(), $advertiserAdTypeFn)
                ->when($user->isPublisher(), $publisherAdTypeFn)
                ->whereBetween('records.time', [now()->yesterday()->startOfDay(), now()->yesterday()->endOfDay()])
                ->first();

            $todayValues = Record::query()
                ->selectRaw("SUM(IF(ads_types.kind='CPC', records.cost != 0, records.cost = 0)) as clicks")
                ->selectRaw("SUM(IF(ads_types.kind='CPC', records.cost = 0, records.cost != 0)) as impressions")
                ->when($user->isManager(), $managerAdTypesFn)
                ->when($user->isAdvertiser(), $advertiserAdTypeFn)
                ->when($user->isPublisher(), $publisherAdTypeFn)
                ->where('records.time', '>=', now()->startOfDay())
                ->first();

            $this->cache['card_stats'] = [
                'CPCDataEachCountries' => $oldValuesEachCountries,
                'clicks' => [
                    $oldValues?->clicks ?? 0,
                    $yesterdayValues?->clicks ?? 0,
                    $todayValues?->clicks ?? 0,
                ],

                'impressions' => [
                    $oldValues?->impressions ?? 0,
                    $yesterdayValues?->impressions ?? 0,
                    $todayValues?->impressions ?? 0,
                ]
            ];
        }
    }

    public function getActiveCampaignsData(?User $user): array {
        $row = \DB::query()
            ->selectRaw("COUNT(*) AS active_campaigns")
            ->selectRaw("IFNULL(SUM(balance),0) AS total_balance")
            ->fromSub(
                \DB::table("invoices_campaigns")
                    ->select('invoices_campaigns.campaign_id')
                    ->selectRaw('IFNULL(SUM(invoices_campaigns.amount - invoices_campaigns.current), 0) balance')
                    ->join('campaigns', function (JoinClause $join) {
                        $join->on('campaigns.id', 'invoices_campaigns.campaign_id');
                        $join->whereNull('campaigns.stopped_at');
                        $join->whereNull('campaigns.deleted_at');
                        $join->where('campaigns.enabled', true);
                    })
                    ->join('ads', function (JoinClause $join) {
                        $join->on('ads.id', 'campaigns.ad_id');
                        $join->whereNotNull('ads.approved_at');
                        $join->whereNull('ads.deleted_at');
                    })
                    ->when($user, function ($query) use ($user) {
                        $query->where('advertiser_id', $user->id);
                    })
                    ->groupBy('invoices_campaigns.campaign_id')
                    ->having('balance', '!=', 0),
                'sub'
            )
            ->first();

        $stats = [
            [
                'value' => 'Balance ' . '$' . number_format($row->total_balance ?? 0, 2)
            ]
        ];

        return [
            'title' => 'Active Campaigns',
            'value' => number_format($row->active_campaigns ?? 0),
            'stats' => $stats
        ];
    }

    public function activeCampaignsCountEachCountry(?User $user): array {        
        $eachCountries = \DB::query()
            ->selectRaw("users.country_id AS country_id")
            ->selectRaw("COUNT(campaign_id) AS active_campaigns")
            ->selectRaw("IFNULL(SUM(balance),0) AS total_balance")
            ->fromSub(
                \DB::table("invoices_campaigns")
                    ->select('invoices_campaigns.campaign_id AS campaign_id')
                    ->selectRaw('IFNULL(ads.advertiser_id, ads.approved_by_id) AS user_id')
                    ->selectRaw('IFNULL((invoices_campaigns.amount - invoices_campaigns.current), 0) balance')
                    ->join('campaigns', function (JoinClause $join) {
                        $join->on('campaigns.id', 'invoices_campaigns.campaign_id');
                        $join->whereNull('campaigns.stopped_at');
                        $join->whereNull('campaigns.deleted_at');
                        $join->where('campaigns.enabled', true);
                    })
                    ->join('ads', function (JoinClause $join) {
                        $join->on('ads.id', 'campaigns.ad_id');
                        $join->whereNotNull('ads.approved_at');
                        $join->whereNull('ads.deleted_at');
                    })
                    ->when($user, function ($query) use ($user) {
                        $query->where('advertiser_id', $user->id);
                    })
                    // ->groupBy('invoices_campaigns.campaign_id')
                    ->having('balance', '!=', 0),
                'sub'
            )
            ->join('users', function (JoinClause $join) {
                $join->on('users.id', 'sub.user_id');
            })
            ->groupBy('users.country_id')
            ->get();

        $campaignsCountEachCountry = [];
        foreach($eachCountries as $eachCountry) {
            $campaignsCountEachCountry[] = [
                'country_id' => $eachCountry->country_id,
                'count' => $eachCountry->active_campaigns,
                'balance' => $eachCountry->total_balance
            ];
        }
        return $campaignsCountEachCountry;
    }

    public function getTotalAdvertisersBalanceData(): array {
        $totalBalance = Invoice::paid()
            ->selectRaw('IFNULL(SUM(invoices.amount), 0) - IFNULL(SUM(invoices_campaigns.amount), 0) AS total')
            ->join('users_advertisers', 'users_advertisers.user_id', 'invoices.user_id')
            ->leftJoinSub(
                InvoiceCampaign::query()
                    ->select('invoice_id')
                    ->selectRaw('SUM(amount) AS amount')
                    ->groupBy('invoice_id'),
                'invoices_campaigns',
                'invoices_campaigns.invoice_id', 'invoices.id'
            )
            ->value('total') ?? 0;

        return [
            'title' => 'Total Advertisers Balance',
            'value' => '$' . number_format($totalBalance, 2),
        ];
    }

    public function getTotalPublishersPaidData(): array {
        $total = Invoice::paid()
            ->whereNotNull('withdrawal_request_id')
            ->sum('invoices.amount');

        return [
            'title' => 'Total Paid to Publishers',
            'value' => '$' . number_format($total, 2),
        ];
    }

    public function getPublishersBalanceData(): array {
        $row = Invoice::query()
            ->selectRaw('SUM(invoices.amount * IFNULL(ads.is_third_party, 0) * (invoices.payment_id IS NULL)) AS third_parties')
            ->selectRaw('SUM(invoices.amount * IFNULL(!ads.is_third_party,0) * (invoices.payment_id IS NULL)) AS regular_ads')
            ->selectRaw('SUM(invoices.amount * (ads.id IS NULL) * (invoices.payment_id IS NULL OR `payments`.`confirmed_at` IS NULL)) AS manual')
            ->join('users_publishers', 'users_publishers.user_id', 'invoices.user_id')
            ->leftJoin('invoices_places', 'invoices_places.invoice_id', 'invoices.id')
            ->leftJoin('campaigns', 'campaigns.id', 'invoices_places.campaign_id')
            ->leftJoin('ads', 'ads.id', 'campaigns.ad_id')
            ->leftJoin('payments', function (JoinClause $join) {
                $join->on('payments.id', 'invoices.payment_id');
                $join->whereNotNull('payments.confirmed_at');
            })
            ->first()
            ?->toArray();

        $row ??= [
            'third_parties' => 0,
            'regular_ads' => 0,
            'manual' => 0,
        ];

        $stats = [
            [
                'value' => 'Regular ' . '$' . number_format($row['regular_ads'] + $row['manual'], 2),
            ],
            [
                'value' => '3rd Party ' . '$' . number_format($row['third_parties'], 2),
            ],
        ];

        return [
            'title' => 'Total Publishers Balance',
            'value' => '$' . number_format(array_sum($row), 2),
            'stats' => $stats
        ];
    }

    public function getTotalAdvertisersEachCountry(): array {
        $eachCountries = \DB::table("users_advertisers")
                ->selectRaw('users.country_id AS country_id')
                ->selectRaw('count(users_advertisers.user_id) AS countTotal')
                ->join('users', function (JoinClause $join) {
                    $join->on('users.id', 'users_advertisers.user_id');
                })
                ->groupBy('users.country_id')
                ->get();
        
        $totalAdvertisersCountEachCountry = [];

        foreach($eachCountries as $eachCountry) {
            $totalAdvertisersCountEachCountry[] = [
                'country_id' => $eachCountry->country_id,
                'count' => $eachCountry->countTotal
            ];
        }
        return $totalAdvertisersCountEachCountry;
    }

    public function getTotalPublishersEachCountry(): array {
        $eachCountries = \DB::table("users_publishers")
                ->selectRaw('users.country_id AS country_id')
                ->selectRaw('count(users_publishers.user_id) AS countTotal')
                ->join('users', function (JoinClause $join) {
                    $join->on('users.id', 'users_publishers.user_id');
                })
                ->groupBy('users.country_id')
                ->get();
        
        $totalPublishersCountEachCountry = [];

        foreach($eachCountries as $eachCountry) {
            $totalPublishersCountEachCountry[] = [
                'country_id' => $eachCountry->country_id,
                'count' => $eachCountry->countTotal
            ];
        }
        return $totalPublishersCountEachCountry;
    }

    public function getTotalAdvertisersBalanceEachCountry() {
        $eachCountries = Invoice::paid()
            ->selectRaw('IFNULL(SUM(invoices.amount), 0) - IFNULL(SUM(invoices_campaigns.amount), 0) AS total, users.country_id')
            ->join('users_advertisers', 'users_advertisers.user_id', 'invoices.user_id')
            ->join('users', 'users.id', 'users_advertisers.user_id') // Join with users table
            ->leftJoinSub(
                InvoiceCampaign::query()
                    ->select('invoice_id')
                    ->selectRaw('SUM(amount) AS amount')
                    ->groupBy('invoice_id'),
                'invoices_campaigns',
                'invoices_campaigns.invoice_id', 'invoices.id'
            )
            ->groupBy('users.country_id') // Group by country_id
            ->get();

        $totalPublishersCountEachCountry = [];

        foreach($eachCountries as $eachCountry) {
            $totalPublishersCountEachCountry[] = [
                'country_id' => $eachCountry->country_id,
                'balance' => number_format($eachCountry->total, 2)
            ];
        }
        return $totalPublishersCountEachCountry;
    }

    public function getTotalPublishersBalanceEachCountry() {
        $eachCountries = Invoice::query()
            ->selectRaw('users.country_id AS country_id')
            ->selectRaw('SUM(invoices.amount * IFNULL(ads.is_third_party, 0) * (invoices.payment_id IS NULL)) AS third_parties')
            ->selectRaw('SUM(invoices.amount * IFNULL(!ads.is_third_party, 0) * (invoices.payment_id IS NULL)) AS regular_ads')
            ->selectRaw('SUM(invoices.amount * (ads.id IS NULL) * (invoices.payment_id IS NULL OR `payments`.`confirmed_at` IS NULL)) AS manual')
            ->join('users_publishers', 'users_publishers.user_id', 'invoices.user_id')
            ->join('users', 'users.id', 'users_publishers.user_id')
            ->leftJoin('invoices_places', 'invoices_places.invoice_id', 'invoices.id')
            ->leftJoin('campaigns', 'campaigns.id', 'invoices_places.campaign_id')
            ->leftJoin('ads', 'ads.id', 'campaigns.ad_id')
            ->leftJoin('payments', function (JoinClause $join) {
                $join->on('payments.id', 'invoices.payment_id');
                $join->whereNotNull('payments.confirmed_at');
            })
            ->groupBy('users.country_id') // Group by country_id
            ->get();
        $totalPublishersCountEachCountry = [];

        foreach($eachCountries as $eachCountry) {
            $totalPublishersCountEachCountry[] = [
                'country_id' => $eachCountry->country_id,
                'third_parties' => number_format($eachCountry->third_parties, 2),
                'regular_ads' => number_format(($eachCountry->regular_ads + $eachCountry->manual), 2),
                'manual' => number_format(($eachCountry->regular_ads + $eachCountry->manual + $eachCountry->third_parties), 2)
            ];
        }
        return $totalPublishersCountEachCountry;
    }
}