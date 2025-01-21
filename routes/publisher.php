<?php

use App\Http\Controllers\Invoices\InvoicesPublishersController;
use App\Http\Controllers\Invoices\PaymentsPublishersController;
use App\Http\Controllers\Places\PlacesLimitedController;
use App\Http\Controllers\Users;

Route::name('publisher.')->prefix('publisher')->middleware(['auth', 'verified', 'only-active-users', 'usertype:Publisher'])->group(function () {

    Route::get('/domains/list', [Users\DomainsController::class, 'list'])->name('domains.list');
    Route::resource('domains', Users\DomainsController::class)->except('create');

    Route::name('places.')->prefix('places')->group(function () {
        Route::get('/', [PlacesLimitedController::class, 'index'])->name('index');
        Route::get('/list/', [PlacesLimitedController::class, 'list'])->name('list');
        Route::get('/show/{place}', [PlacesLimitedController::class, 'show'])->name('show');
        Route::get('/create', [PlacesLimitedController::class, 'create'])->name('create');
        Route::get('/edit/{place}', [PlacesLimitedController::class, 'edit'])->name('edit');
        Route::post('/', [PlacesLimitedController::class, 'store'])->name('store');
        Route::put('/update/{place}', [PlacesLimitedController::class, 'update'])->name('update');
        Route::delete('/destroy/{place}', [PlacesLimitedController::class, 'destroy'])->name('destroy');
    });

    Route::name('invoices.')->prefix('payments')->group(function () {
        Route::get('/', [InvoicesPublishersController::class, 'index'])->name('index');
        Route::get('/show/{invoice}', [InvoicesPublishersController::class, 'show'])->name('show');
        Route::get('/list/{key}', [InvoicesPublishersController::class, 'list'])->name('list');
        Route::put('/{invoice}/archive/{archive}', [InvoicesPublishersController::class, 'archive'])->name('archive');

        Route::get('/withdrawal', [InvoicesPublishersController::class, 'withdrawalForm'])->name('withdrawalForm');
        Route::post('/withdrawal', [InvoicesPublishersController::class, 'withdrawal'])->name('withdrawal');
    });

    Route::name('payments.')->prefix('archived-payments')->group(function () {
        Route::get('/', [PaymentsPublishersController::class, 'index'])->name('index');
        Route::get('/show/{payment}', [PaymentsPublishersController::class, 'show'])->name('show');
        Route::get('/list', [PaymentsPublishersController::class, 'list'])->name('list');
    });

    Route::name('withdrawals.')->prefix('withdrawals')->group(function () {
        Route::get('/show/{withdrawal}', [InvoicesPublishersController::class, 'withdrawalShow'])->name('withdrawalShow');
        Route::delete('/{withdrawal}', [InvoicesPublishersController::class, 'withdrawalDestroy'])->name('withdrawalDestroy');

        Route::get('/{withdrawal}/invoices', [InvoicesPublishersController::class, 'withdrawalInvoicesIndex'])->name('withdrawalInvoicesIndex');
        Route::get('/{withdrawal}/invoices/list', [InvoicesPublishersController::class, 'withdrawalInvoicesList'])->name('withdrawalInvoicesList');
    });
});
