<?php

use App\Http\Controllers\Ads\AdsController;
use App\Http\Controllers\Ads\CampaignsController;
use App\Http\Controllers\Ads\CampaignsCountriesController;
use App\Http\Controllers\AdTypesController;
use App\Http\Controllers\BlacklistingController;
use App\Http\Controllers\Categories\CategoriesController;
use App\Http\Controllers\Categories\CategoriesCountriesController;
use App\Http\Controllers\CountriesController;
use App\Http\Controllers\CurrenciesController;
use App\Http\Controllers\EmailsTemplates\EmailsTemplatesController;
use App\Http\Controllers\Invoices\InvoicesController;
use App\Http\Controllers\Places\PlacesController;
use App\Http\Controllers\PromosController;
use App\Http\Controllers\Users;
use Illuminate\Support\Facades\Route;

// Admin Routes
Route::name('admin.')->prefix('admin')->middleware(['auth', 'verified', 'only-active-users', 'usertype:Admin'])->group(function () {
    Route::resource('/managers', Users\ManagersController::class)->except('create');
    Route::get('/managers/{manager}/login', [Users\ManagersController::class, 'loginBehalf'])->name('managers.login');
    Route::put('/managers/{manager}/activate/{active}', [Users\ManagersController::class, 'activate'])->name('managers.activate');
    Route::get('/managers/list/{key}', [Users\ManagersController::class, 'list'])->name('managers.list');
    Route::get('/managers/permissions/{manager}', [Users\ManagersController::class, 'permissions'])->name('managers.permissions');
    Route::put('/managers/change_permissions/{manager}', [Users\ManagersController::class, 'change_permissions'])->name('managers.change_permissions');

    Route::get('/currencies/list', [CurrenciesController::class, 'list'])->name('currencies.list');
    Route::put('/currencies/{currency}/activate/{active}', [CurrenciesController::class, 'activate'])->name('currencies.activate');
    Route::resource('currencies', CurrenciesController::class)->except(['create']);

    Route::get('/ad-types/list', [AdTypesController::class, 'list'])->name('ad-types.list');
    Route::put('/ad-types/{ad_type}/activate/{active}', [AdTypesController::class, 'activate'])->name('ad-types.activate');
    Route::resource('ad-types', AdTypesController::class)->except('create');

    Route::post('/campaigns-change-state', [AdsController::class, 'changeState'])->name('campaigns.change-state');

    Route::get('/categories/list', [CategoriesController::class, 'list'])->name('categories.list');
    Route::put('/categories/{category}/activate/{active}', [CategoriesController::class, 'activate'])->name('categories.activate');
    Route::resource('categories', CategoriesController::class)->except('create');

    Route::get('/blacklisting/list', [BlacklistingController::class, 'list'])->name('blacklisting.list');
    Route::resource('blacklisting', BlacklistingController::class)->except(['create', 'show']);

    Route::get('/categories/countries/{category}', [CategoriesCountriesController::class, 'index'])->name('categories.countries.index');
    Route::put('/categories/countries/{category}/update/', [CategoriesCountriesController::class, 'update'])->name('categories.countries.update');

    Route::name('domains.')->prefix('domains')->group(function () {
        Route::get('/', [Users\DomainsController::class, 'indexUsers'])->name('indexUsers');
        Route::get('/list/{key}', [Users\DomainsController::class, 'listUsers'])->name('listUsers');
        Route::post('/{user}', [Users\DomainsController::class, 'store'])->name('store');
        Route::get('/show/{domain}', [Users\DomainsController::class, 'show'])->name('show');
        Route::get('/edit/{domain}', [Users\DomainsController::class, 'edit'])->name('edit');
        Route::put('/update/{domain}', [Users\DomainsController::class, 'update'])->name('update');
        Route::delete('/destroy/{domain}', [Users\DomainsController::class, 'destroy'])->name('destroy');
        Route::put('/{domain}/approve/{approve}', [Users\DomainsController::class, 'approve'])->name('approve');
    });

    Route::name('admin-domains.')->prefix('admin-domains')->group(function () {
        Route::get('/', [Users\AdminsDomainsController::class, 'index'])->name('index');
        Route::get('/list/', [Users\AdminsDomainsController::class, 'list'])->name('list');
        Route::post('/', [Users\AdminsDomainsController::class, 'store'])->name('store');
        Route::get('/show/{domain}', [Users\AdminsDomainsController::class, 'show'])->name('show');
        Route::get('/edit/{domain}', [Users\AdminsDomainsController::class, 'edit'])->name('edit');
        Route::put('/update/{domain}', [Users\AdminsDomainsController::class, 'update'])->name('update');
        Route::delete('/destroy/{domain}', [Users\AdminsDomainsController::class, 'destroy'])->name('destroy');
        Route::put('/{domain}/approve/{approve}', [Users\AdminsDomainsController::class, 'approve'])->name('approve');
    });

    Route::name('countries.')->prefix('geo-profiles')->group(function () {
        Route::get('/', [CountriesController::class, 'index'])->name('index');
        Route::get('/edit/{country}', [CountriesController::class, 'edit'])->name('edit');
        Route::get('/list/{tier}', [CountriesController::class, 'list'])->name('list');
        Route::put('/{country}/visibility/{hide}', [CountriesController::class, 'visibility'])->name('visibility');
        Route::put('/update/{country}', [CountriesController::class, 'update'])->name('update');
    });
});

// Manager (and Admin) Routes
Route::name('admin.')->prefix('admin')->middleware(['auth', 'verified', 'only-active-users', 'usertype:Manager'])->group(function () {
    Route::resource('/advertisers', Users\AdvertisersController::class)->except('create');
    Route::get('/advertisers/{advertiser}/login', [Users\AdvertisersController::class, 'loginBehalf'])->name('advertisers.login');
    Route::put('/advertisers/{advertiser}/activate/{active}', [Users\AdvertisersController::class, 'activate'])->name('advertisers.activate');
    Route::get('/advertisers/list/{key}', [Users\AdvertisersController::class, 'list'])->name('advertisers.list');

    Route::resource('/publishers', Users\PublishersController::class)->except('create');
    Route::get('/publishers/{publisher}/login', [Users\PublishersController::class, 'loginBehalf'])->name('publishers.login');
    Route::put('/publishers/{publisher}/activate/{active}', [Users\PublishersController::class, 'activate'])->name('publishers.activate');
    Route::get('/publishers/list/{key}', [Users\PublishersController::class, 'list'])->name('publishers.list');

    Route::get('/promos/list', [PromosController::class, 'list'])->name('promos.list');
    Route::resource('promos', PromosController::class)->except('create');

    Route::name('invoices.')->prefix('invoices')->group(function () {
        Route::get('/', [InvoicesController::class, 'indexUsers'])->name('indexUsers');
        Route::get('/list-users/{key}', [InvoicesController::class, 'listUsers'])->name('listUsers');

        Route::get('/{user}', [InvoicesController::class, 'index'])->name('index');
        Route::get('/show/{invoice}', [InvoicesController::class, 'show'])->name('show');
        Route::get('/{user}/list', [InvoicesController::class, 'list'])->name('list');
        Route::post('/{user}', [InvoicesController::class, 'store'])->name('store');
    });

    Route::name('ads.')->prefix('ads')->group(function () {
        Route::get('/', [AdsController::class, 'advertisersIndex'])->name('advertisers-index');
        Route::get('/advertisers-list/{key}', [AdsController::class, 'advertisersList'])->name('advertisers-list');

        Route::get('/index/{advertiser?}', [AdsController::class, 'index'])->name('index');
        Route::get('/list/{advertiser?}', [AdsController::class, 'list'])->name('list');
        Route::get('/show/{ad}', [AdsController::class, 'show'])->name('show');
        Route::get('/edit/{ad}', [AdsController::class, 'edit'])->name('edit');
        Route::post('{advertiser?}', [AdsController::class, 'store'])->name('store');
        Route::put('/update/{ad}', [AdsController::class, 'update'])->name('update');
        Route::delete('/destroy/{ad}', [AdsController::class, 'destroy'])->name('destroy');
        Route::put('/{ad}/approve/{approve}', [AdsController::class, 'approve'])->name('approve');

        Route::name('campaigns.')->prefix('campaigns')->group(function () {
            Route::get('/{ad}', [CampaignsController::class, 'index'])->name('index');
            Route::get('/{ad}/list', [CampaignsController::class, 'list'])->name('list');
            Route::get('/show/{campaign}', [CampaignsController::class, 'show'])->name('show');
            Route::get('/{ad}/create', [CampaignsController::class, 'create'])->name('create');
            Route::get('/edit/{campaign}', [CampaignsController::class, 'edit'])->name('edit');
            Route::post('/{ad}', [CampaignsController::class, 'store'])->name('store');
            Route::put('/update/{campaign}', [CampaignsController::class, 'update'])->name('update');
            Route::delete('/destroy/{campaign}', [CampaignsController::class, 'destroy'])->name('destroy');
            Route::put('/stop/{campaign}/{stop}', [CampaignsController::class, 'stop'])->name('stop');
            Route::put('/enable/{campaign}/{enable}', [CampaignsController::class, 'enable'])->name('enable');

            Route::name('countries.')->prefix('countries')->group(function () {
                Route::get('/{campaign}', [CampaignsCountriesController::class, 'index'])->name('index');
                Route::put('/{campaign}/update/', [CampaignsCountriesController::class, 'update'])->name('update');
            });
        });
    });

    Route::name('emails-templates.')->prefix('emails-templates')->group(function () {
        Route::get('/', [EmailsTemplatesController::class, 'index'])->name('index');
        Route::get("/list", [EmailsTemplatesController::class, "list"])->name("list");
        Route::get('/show/{template}', [EmailsTemplatesController::class, 'show'])->name('show');
        Route::get("/{template}/edit/", [EmailsTemplatesController::class, "edit"])->name("edit");
        Route::get("/{template}/send-form/", [EmailsTemplatesController::class, "sendForm"])->name("send-form");
        Route::post('/', [EmailsTemplatesController::class, 'store'])->name('store');
        Route::put("/send/{template}", [EmailsTemplatesController::class, "send"])->name("send");
        Route::put("/update/{template}", [EmailsTemplatesController::class, "update"])->name("update");
        Route::delete("/{template}", [EmailsTemplatesController::class, "destroy"])->name("destroy");
    });

    Route::name('domains.')->prefix('domains')->group(function () {
        Route::get('/', [Users\DomainsController::class, 'indexUsers'])->name('indexUsers');
        Route::get('/list/{key}', [Users\DomainsController::class, 'listUsers'])->name('listUsers');
        Route::post('/{user}', [Users\DomainsController::class, 'store'])->name('store');
        Route::get('/show/{domain}', [Users\DomainsController::class, 'show'])->name('show');
        Route::get('/edit/{domain}', [Users\DomainsController::class, 'edit'])->name('edit');
        Route::put('/update/{domain}', [Users\DomainsController::class, 'update'])->name('update');
        Route::delete('/destroy/{domain}', [Users\DomainsController::class, 'destroy'])->name('destroy');
        Route::put('/{domain}/approve/{approve}', [Users\DomainsController::class, 'approve'])->name('approve');

        //Route::get('/list/{key}', [Users\DomainsController::class, 'list'])->name('list');
    });

    Route::name('advertisers.domains.')->prefix('advertisers')->group(function () {
        Route::get('{advertiser}/domains', [Users\AdvertisersController::class, 'domains'])->name('index');
        Route::get('{advertiser}/domains/list', [Users\AdvertisersDomainsController::class, 'list'])->name('list');
        Route::post('{advertiser}/domains', [Users\AdvertisersDomainsController::class, 'store'])->name('store');
        Route::get('/domains/show/{domain}', [Users\AdvertisersDomainsController::class, 'show'])->name('show');
        Route::get('/domains/edit/{domain}', [Users\AdvertisersDomainsController::class, 'edit'])->name('edit');
        Route::put('/domains/update/{domain}', [Users\AdvertisersDomainsController::class, 'update'])->name('update');
        Route::delete('/domains/destroy/{domain}', [Users\AdvertisersDomainsController::class, 'destroy'])->name('destroy');
        Route::put('/domains/{domain}/approve/{approve}', [Users\AdvertisersDomainsController::class, 'approve'])->name('approve');
    });

    Route::name('publishers.domains.')->prefix('publishers')->group(function () {
        Route::get('{publisher}/domains', [Users\PublishersController::class, 'domains'])->name('index');
        Route::get('{publisher}/domains/list', [Users\PublishersDomainsController::class, 'list'])->name('list');
        Route::post('{publisher}/domains', [Users\PublishersDomainsController::class, 'store'])->name('store');
        Route::get('/domains/show/{domain}', [Users\PublishersDomainsController::class, 'show'])->name('show');
        Route::get('/domains/edit/{domain}', [Users\PublishersDomainsController::class, 'edit'])->name('edit');
        Route::put('/domains/update/{domain}', [Users\PublishersDomainsController::class, 'update'])->name('update');
        Route::delete('/domains/destroy/{domain}', [Users\PublishersDomainsController::class, 'destroy'])->name('destroy');
        Route::put('/domains/{domain}/approve/{approve}', [Users\PublishersDomainsController::class, 'approve'])->name('approve');
    });

    Route::name('places.')->prefix('places')->group(function () {
        Route::get('/', [PlacesController::class, 'publishersIndex'])->name('publishers-index');
        Route::get('/list/{key}', [PlacesController::class, 'publishersList'])->name('publishers-list');
        Route::get('/{publisher}/index', [PlacesController::class, 'index'])->name('index');
        Route::get('/{publisher}/list', [PlacesController::class, 'list'])->name('list');
        Route::get('/show/{place}', [PlacesController::class, 'show'])->name('show');
        Route::get('/{publisher}/create', [PlacesController::class, 'create'])->name('create');
        Route::get('/edit/{place}', [PlacesController::class, 'edit'])->name('edit');
        Route::post('/', [PlacesController::class, 'store'])->name('store');
        Route::put('/update/{place}', [PlacesController::class, 'update'])->name('update');
        Route::delete('/destroy/{place}', [PlacesController::class, 'destroy'])->name('destroy');
        Route::put('/{place}/approve/{approve}', [PlacesController::class, 'approve'])->name('approve');
    });

    Route::name('withdrawals.')->prefix('withdrawals')->group(function () {
        Route::get('/{user}', [InvoicesController::class, 'withdrawalsIndex'])->name('withdrawalsIndex');
        Route::get('/{user}/list', [InvoicesController::class, 'withdrawalsList'])->name('withdrawalsList');
        Route::get('/show/{withdrawal}', [InvoicesController::class, 'withdrawalShow'])->name('withdrawalShow');
        Route::get('/pay/{withdrawal}', [InvoicesController::class, 'withdrawalForm'])->name('withdrawalForm');
        Route::put('/{withdrawal}', [InvoicesController::class, 'withdrawal'])->name('withdrawal');
        Route::put('/confirm/{withdrawal}', [InvoicesController::class, 'confirm'])->name('confirm');
        Route::delete('/{withdrawal}', [InvoicesController::class, 'withdrawalDestroy'])->name('withdrawalDestroy');
        Route::get('/{withdrawal}/invoices', [InvoicesController::class, 'withdrawalInvoicesIndex'])->name('withdrawalInvoicesIndex');
        Route::get('/{withdrawal}/invoices/list', [InvoicesController::class, 'withdrawalInvoicesList'])->name('withdrawalInvoicesList');
    });
});
