<?php


namespace App\Services;

use App\Helpers\Helper;
use Jenssegers\Agent\Agent;

class FirewallService implements FirewallServiceInterface {
    public function isProxyRequest(string $ip, int $ipTimestamp, int $clientTimestamp): bool {
        return Helper::proxyCheck($ip);

//        // Check timezone
//        if (abs($ipTimestamp - $clientTimestamp) > config('ads.footprint_throttle.max_timezone_tolerance')) {
//            return true;
//        }
//
//        // Check API
//        $response = Http::withOptions(config('ads.http_client_options'))->get('https://vpnapi.io/api/' . $ip, ['key' => config('ads.vpnapi_key')]);
//        if ($response->successful()) {
//            $security = $response->json('security');
//            return $security && ($security['vpn'] || $security['proxy'] || $security['tor']);
//        }
//
//        return false;
    }

    public function isMobileRequest(string $userAgent): bool {
        return preg_match('/(Android|webOS|iPhone|iPad|iPod|BlackBerry|Windows Phone)/i', $userAgent) === 1
            || $this->getAgent($userAgent)->isMobile();
    }

    public function getAgent(string $userAgent = null, array $headers = null): Agent {
        return new Agent($headers, $userAgent);
    }
}