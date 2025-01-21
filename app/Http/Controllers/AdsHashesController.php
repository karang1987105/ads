<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Models\Place;
use DB;
use Log;
use Str;

class AdsHashesController {

    public static function isHashValid(Campaign $campaign, Place $place, $ip, $hash, bool $click): bool {
        $result = DB::table("ads_hashes")
            ->where('campaign_id', '=', $campaign->id)
            ->where('place_id', '=', $place->id)
            ->where('ip', '=', $ip)
            ->where('hash', '=', $hash)
            ->where('click', '=', $click)
            ->where('expiry', '>=', time())
            ->delete();

        Log::info("Hash check: Campaign:$campaign->id, Place:$place->id, IP:$ip, Hash:$hash, Click:" . (int)$click . " Time:" . time() . ", Result:" . $result);

        // View allowed for CPC
        if (!$click && $campaign->ad->adType->isCPC()) {
            $result = 1;
        }

        return $result === 1;
    }

    public static function registerNewAdHash($campaignId, $placeId, string $ip) {
        $hash = md5($campaignId . ',' . $placeId . ',' . $ip . ',' . Str::random());
        $expiry = time() + (60 * config('ads.footprint_throttle.in_minutes') / config('ads.footprint_throttle.max_requests'));
        DB::table("ads_hashes")->insert([
            ['campaign_id' => $campaignId, 'place_id' => $placeId, 'ip' => $ip, 'hash' => $hash, 'expiry' => $expiry, 'click' => 1],
            ['campaign_id' => $campaignId, 'place_id' => $placeId, 'ip' => $ip, 'hash' => $hash, 'expiry' => $expiry, 'click' => 0],
        ]);
        return $hash;
    }

    public static function cleanup() {
        $results = DB::table('ads_hashes')->where('expiry', '<', time())->delete();
        Log::info("Cleaning up expired ads_hashes: " . $results);
    }
}
