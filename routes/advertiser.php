<?php

use App\Http\Controllers\Ads\AdsLimitedController;
use App\Http\Controllers\Ads\CampaignsCountriesController;
use App\Http\Controllers\Ads\CampaignsLimitedController;
use App\Http\Controllers\Invoices\InvoicesAdvertisersController;
use App\Http\Controllers\Users;
use Illuminate\Support\Facades\Route;

Route::name('advertiser.')->prefix('advertiser')->middleware(['auth', 'verified', 'only-active-users', 'usertype:Advertiser'])->group(function () {

    Route::get('/domains/list', [Users\DomainsController::class, 'list'])->name('domains.list');
    Route::resource('domains', Users\DomainsController::class)->except('create');

    Route::get('/ads/list', [AdsLimitedController::class, 'list'])->name('ads.list');
    Route::resource('ads', AdsLimitedController::class);

    Route::name('ads.campaigns.')->prefix('ads/campaigns')->group(function () {
        Route::get('/{ad}', [CampaignsLimitedController::class, 'index'])->name('index');
        Route::get('/{ad}/list', [CampaignsLimitedController::class, 'list'])->name('list');
        Route::get('/show/{campaign}', [CampaignsLimitedController::class, 'show'])->name('show');
        Route::get('/{ad}/create', [CampaignsLimitedController::class, 'create'])->name('create');
        Route::get('/edit/{campaign}', [CampaignsLimitedController::class, 'edit'])->name('edit');
        Route::post('/{ad}', [CampaignsLimitedController::class, 'store'])->name('store');
        Route::put('/update/{campaign}', [CampaignsLimitedController::class, 'update'])->name('update');
        Route::delete('/destroy/{campaign}', [CampaignsLimitedController::class, 'destroy'])->name('destroy');
        Route::put('/enable/{campaign}/{enable}', [CampaignsLimitedController::class, 'enable'])->name('enable');

        Route::name('countries.')->prefix('countries')->group(function () {
            Route::get('/{campaign}', [CampaignsCountriesController::class, 'index'])->name('index');
            Route::put('/{campaign}/update/', [CampaignsCountriesController::class, 'update'])->name('update');
        });
    });

    Route::name('invoices.')->prefix('invoices')->group(function () {
        Route::get('/', [InvoicesAdvertisersController::class, 'index'])->name('index');
        Route::get('/create', [InvoicesAdvertisersController::class, 'create'])->name('create');
        Route::get('/show/{invoice}', [InvoicesAdvertisersController::class, 'show'])->name('show');
        Route::get('/list/{key}', [InvoicesAdvertisersController::class, 'list'])->name('list');
        Route::post('/', [InvoicesAdvertisersController::class, 'store'])->name('store');
        Route::put('/{invoice}/archive/{archive}', [InvoicesAdvertisersController::class, 'archive'])->name('archive');

        Route::get('/promos/verify/', [InvoicesAdvertisersController::class, 'verifyPromo'])->name('verify-promo');
    });
});
