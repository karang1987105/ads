<?php

namespace App\Providers;

use App\Services\FirewallService;
use App\Services\FirewallServiceInterface;
use App\Services\SiteNotificationsService;
use Illuminate\Support\Collection;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider {
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register() {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot() {
        Collection::macro('toString', function ($callback = null) {
            $callback = $callback ?? fn($item) => '' . $item;
            return $this->map($callback)->join('');
        });

        $this->app->instance(SiteNotificationsService::class, new SiteNotificationsService);
        $this->app->singleton(FirewallServiceInterface::class, fn() => new FirewallService);
    }
}
