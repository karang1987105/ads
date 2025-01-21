<?php

namespace App\Jobs;

use App\Helpers\Helper;
use App\Http\Controllers\AdsHashesController;
use App\Models\Campaign;
use App\Models\Country;
use App\Models\Invoice;
use App\Models\InvoicePlace;
use App\Models\Place;
use App\Models\Record;
use App\Models\Setting;
use App\Services\FirewallService;
use DB;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Log;
use stdClass;

class AdEventQueue implements ShouldQueue {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private string $placeUid;
    private string $campaignUid;
    private array $clientInfo;

    public function __construct(string $placeUid, string $campaignUid, array $clientInfo) {
        $this->placeUid = $placeUid;
        $this->campaignUid = $campaignUid;
        $this->clientInfo = $clientInfo;
    }

    public function handle() {
        $debug = config('app.debug');
        Log::info("DEBUG: " . ($debug ? 'true' : 'false'));

        if (Setting::value(Setting::CAMPAIGNS_STOP)) {
            return;
        }

        /**
         * @var Place $place
         * @var Campaign $campaign
         * @var Country $country
         */
        $place = Place::uuid($this->placeUid);
        $campaign = Campaign::uuid($this->campaignUid);

        $err = [];
        if (!isset($place) || !$place->isApproved() || !$place->adType->active || !$place->domain->isApproved() || !$place->domain->publisher->isActive()) {
            if (!isset($place)) {
                $err[] = "Place $this->placeUid is not valid";
            }
            if (!$place->isApproved()) {
                $err[] = "Place#$place->id is not approved";
            }
            if (!$place->adType->active) {
                $err[] = "Place#$place->id ad type is not active";
            }
            if (!$place->domain->isApproved()) {
                $err[] = "Place#$place->id domain is not approved";
            }
            if (!$place->domain->publisher->isActive()) {
                $err[] = "Place#$place->id publisher is suspended";
            }

            $place = null;
        }
        if (!isset($campaign) || !$campaign->isActive() || $campaign->enabled !== true || !$campaign->ad->isApproved()
            || (!$campaign->ad->isAdminsAd() && !$campaign->ad->advertiser->load('user:id,active')->isActive())
            || (!$campaign->ad->isThirdParty() && !$campaign->ad->getDomain()?->isApproved())) {
            if (!isset($campaign)) {
                $err[] = "Campaign $this->campaignUid is not valid";
            }
            if (!$campaign->isActive()) {
                $err[] = "Campaign#$campaign->id is not active";
            }
            if ($campaign->enabled !== true) {
                $err[] = "Campaign#$campaign->id is not enabled";
            }
            if (!$campaign->ad->isApproved()) {
                $err[] = "Campaign#$campaign->id ad is not approved";
            }
            if (!$campaign->ad->isAdminsAd() && !$campaign->ad->advertiser->isActive()) {
                $err[] = "Campaign#$campaign->id advertiser is suspended";
            }
            if (!$campaign->ad->isThirdParty() && !$campaign->ad->getDomain()?->isApproved()) {
                $err[] = "Campaign#$campaign->id ad domain is not active";
            }

            $campaign = null;
        }

        if (!empty($err)) {
            Log::info(json_encode($err));
        }

        if (isset($place, $campaign)) {
            $firewall = app(FirewallService::class);
            $ip = $this->clientInfo['ip'];
            $clicked = $this->clientInfo['click'];

            Log::info("Trigger: Campaign#$campaign->id, Place#$place->id, ClientInfo:" . json_encode($this->clientInfo));

            // Validate requests by hash
            if (!AdsHashesController::isHashValid($campaign, $place, $ip, $this->clientInfo['hash'], $clicked)) {
                Log::info("Invalid/Expired hash sent for Campaign#$campaign->id on Place#$place->id");
                return;
            }

            // Check country by IP
            if (!$debug) {
                $country = Country::getCodeByIp($ip);
            } else {
                $country = Country::find(config('ads.localhost_country'));
            }
            $countryCode = $country?->id;
            if ($country === null) {
                Log::info("Unknown/Invalid client ip [$ip] for Campaign#$campaign->id on Place#$place->id");
                return;
            }

            // Check proxy if needed
            if (!$campaign->proxy) {
                if ($firewall->isProxyRequest($ip, $country->getCurrentTimestamp(), $this->clientInfo['time'])) {
                    Log::info("Proxy detected for Campaign#$campaign->id on Place#$place->id");
                    return;
                }
            }

            // Device check
            if ($campaign->device !== 'All' && ($firewall->isMobileRequest($this->clientInfo['useragent']) ? $campaign->device === 'Desktop' : $campaign->device === 'Mobile')) {
                return;
            }

            // Country allowed by advertiser
            if (!$campaign->isAvailableInCountry($countryCode)) {
                Log::info("Request from unchecked country ($countryCode) for Campaign#$campaign->id on Place#$place->id");
                return;
            }

            // Check publisher domain
            if (!$debug && !Helper::isDomainValid($place->domain, $this->clientInfo['domain'])) {
                Log::info("Place domain ({$place->domain->domain}) doesn't match client referer domain {$this->clientInfo['domain']} for Campaign#$campaign->id on Place#$place->id");
                return;
            }

            // Impress CPC on click
            $campaign->impressed($place->id, $ip, false, $clicked && $campaign->ad->adType->isCPC());

            $withInvoices = match ($campaign->ad->adType->kind) {
                'CPC' => $clicked,
                'CPM', 'CPV' => !$clicked
            };

            $advertiserCost = $publisherShare = null;
            if ($withInvoices) {
                // Check budget
                $advertiserCost = self::getAdvertiserCost($campaign, $countryCode);
                if ($advertiserCost > $campaign->getBalance()) {
                    Log::info("There is no enough budget for Campaign#$campaign->id on Place#$place->id");
                    return;
                }

                // Check category and publisher share
                $publisherShare = self::getPublisherShare($place, $campaign, $countryCode);
                if (!isset($publisherShare)) {
                    Log::info("There is no category for Place#$place->id");
                    return;
                }
            }

            $record = new Record([
                'campaign_id' => $campaign->id,
                'place_id' => $place->id,
                'country_id' => $countryCode,
                'cost' => $advertiserCost ?? 0.0,
                'revenue' => (isset($publisherShare) ? $publisherShare->revenue_share : 0) * $campaign->revenue_ratio,
                'time' => now()
            ]);

            DB::transaction(function () use ($campaign, $place, $advertiserCost, $publisherShare, $record, $withInvoices) {
                try {
                    $record->save();

                    if ($withInvoices) {
                        // Decrease campaign budget
                        foreach ($campaign->getUnbalancedInvoices() as $ic) {
                            $available = $ic->getBalance();

                            if ($available >= $advertiserCost) {
                                $ic->increment('current', $advertiserCost);
                                break;
                            }

                            $ic->increment('current', $available);
                            $advertiserCost -= $available;
                        }

                        // Campaign not expired anymore
                        if ($campaign->notification_sent) {
                            $campaign->update(['notification_sent' => false]);
                        }

                        // Increase publisher earn
                        Log::info('getPublisherShare: ' . $publisherShare->amount . ', ' . ($publisherShare->revenue_share * 100) . '% * ' . $campaign->revenue_ratio);
                        self::getPublisherInvoice($place, $campaign)->increment('amount', $publisherShare->amount * $publisherShare->revenue_share * $campaign->revenue_ratio);
                    }
                    Log::info("AdEvent processed successfully for Place#$place->id, Campaign#$campaign->id");
                } catch (Exception $e) {
                    Log::warning("Exception for Campaign#$campaign->id on Place#$place->id:" . $e->getMessage() . "\n" . $e->getTraceAsString());
                }
            });
        }
    }

    private static function getPublisherInvoice(Place $place, Campaign $campaign): Invoice {
        $invoice = $place->getUnpaidInvoice($campaign);
        if ($invoice === null) {
            $invoice = new Invoice(['title' => $place->title ?? 'System Generated', 'amount' => 0, 'user_id' => $place->publisher()->user_id]);
            $invoice->save();
            $invoice->place()->save(new InvoicePlace(['place_id' => $place->id, 'campaign_id' => $campaign->id]));
        }
        return $invoice;
    }

    private static function getAdvertiserCost(Campaign $campaign, string $countryCode): float {
        $category = $campaign->category;
        $categoryCountry = $category->getCountry($countryCode);
        $cost = ($categoryCountry !== null ? $categoryCountry : $category)->{strtolower($campaign->ad->adType->kind)};
        if ($campaign->ad->adType->kind === 'CPM') {
            $cost = round($cost / config('ads.cpm_mile_size'), 9);
        }
        return round($cost, 9);
    }

    private static function getPublisherShare(Place $place, Campaign $campaign, string $countryCode): stdClass|null {
        $place->domain->load('category');
        $category = $place->domain->category;
        if ($category !== null) {
            $categoryCountry = $category->getCountry($countryCode);
            $amount = ($categoryCountry !== null ? $categoryCountry : $category)->{strtolower($campaign->ad->adType->kind)};
            $share = new stdClass;
            $share->revenue_share = $category->revenue_share / 100;
            $share->amount = $amount;
            if ($campaign->ad->adType->kind === 'CPM') {
                $share->amount = $share->amount / config('ads.cpm_mile_size');
            }
            return $share;
        }
        return null;
    }
}
