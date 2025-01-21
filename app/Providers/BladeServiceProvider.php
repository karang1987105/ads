<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class BladeServiceProvider extends ServiceProvider {
    private const CLOSE = '<' . '?php } ?>';

    private static function OPEN(string $condition): string {
        return '<' . '?php if (' . $condition . ') { ?>';
    }

    public function boot() {
        Blade::directive('admin', fn() => self::OPEN('App\Helpers\Helper::isAdmin()'));
        Blade::directive('endAdmin', fn() => self::CLOSE);

        Blade::directive('manager', fn($permissions) => self::OPEN('App\Helpers\Helper::isManager(' . $permissions . ')'));
        Blade::directive('endManager', fn() => self::CLOSE);

        Blade::directive('advertiser', fn() => self::OPEN('App\Helpers\Helper::isAdvertiser()'));
        Blade::directive('endAdvertiser', fn() => self::CLOSE);

        Blade::directive('publisher', fn() => self::OPEN('App\Helpers\Helper::isPublisher()'));
        Blade::directive('endPublisher', fn() => self::CLOSE);

        Blade::directive('userType', fn($types) => self::OPEN('App\Helpers\Helper::isUserType(' . $types . ')'));
        Blade::directive('endUserType', fn() => self::CLOSE);
    }
}
