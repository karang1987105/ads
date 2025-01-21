<?php


namespace App\Services;

use Jenssegers\Agent\Agent;

interface FirewallServiceInterface {
    public function isProxyRequest(string $ip, int $ipTimestamp, int $clientTimestamp): bool;

    public function isMobileRequest(string $userAgent): bool;

    public function getAgent(string $userAgent = null, array $headers = null): Agent;
}