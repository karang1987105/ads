<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Invoices\InvoicesPublishersController;
use App\Http\Controllers\Logs\LoginAttemptsController;
use App\Http\Controllers\Logs\LogsController;
use App\Http\Controllers\Records\RecordsController;
use App\Http\Controllers\ScriptsController;
use App\Http\Controllers\TicketsController;
use App\Http\Controllers\Users\ProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::name('dashboard')->middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index']);
});

Route::name('profile.')->prefix('profile')->middleware(['auth', 'verified'])->group(function () {
    Route::get('/', [ProfileController::class, 'index'])->name('index');
    Route::put('/update', [ProfileController::class, 'update'])->name('update');
});

Route::name('scripts.')->prefix('s')->middleware(['web', 'firewall'])->group(function () {
    Route::get("/i/{uuid}", [ScriptsController::class, 'init'])->name('init');
    Route::get("/s/{uuid}", [ScriptsController::class, 'show'])->name('show');
    Route::get("/t/{place}/{campaign}", [ScriptsController::class, 'trigger'])->name('trigger');
    Route::get("/test", [ScriptsController::class, 'test'])->name('test');
    Route::get("/vast/{place}/{campaign}", [ScriptsController::class, 'vast'])->name('vast');
});

Route::middleware(['web', 'firewall'])->get('refresh-captcha', fn() => response(captcha_img()));

Route::name('contact.')->prefix('contact')->middleware(['web', 'firewall'])->group(function () {
    Route::get('/', [TicketsController::class, 'createGuest'])->name('create');
    Route::post('/', [TicketsController::class, 'sendGuest'])->name('send');
    Route::get('/{thread}/{hash}', [TicketsController::class, 'createReplyGuest'])->name('create-reply');
    Route::post('/{thread}/{hash}', [TicketsController::class, 'replyGuest'])->name('reply');
});

Route::name('tickets.')->prefix('tickets')->middleware(['auth', 'verified'])->group(function () {
    Route::name('threads.')->prefix('threads')->group(function () {
        Route::get('/', [TicketsController::class, 'threadsIndex'])->name('index');
        Route::get('/list/{key}', [TicketsController::class, 'threadsList'])->name('list');
        Route::get('/show/{thread}', [TicketsController::class, 'threadShow'])->name('show');
        Route::delete('/destroy/{thread}', [TicketsController::class, 'threadDestroy'])->name('destroy');
        Route::put('/close/{thread}/{close}', [TicketsController::class, 'closeThread'])->name('close');
        Route::post('/', [TicketsController::class, 'threadStore'])->name('store');
    });
    Route::name('messages.')->prefix('messages')->group(function () {
        Route::get('/{thread}', [TicketsController::class, 'messagesIndex'])->name('index');
        Route::get('/{thread}/list/', [TicketsController::class, 'messagesList'])->name('list');
        Route::get('/show/{message}', [TicketsController::class, 'messageShow'])->name('show');
        Route::get('/edit/{message}', [TicketsController::class, 'messageEdit'])->name('edit');
        Route::put('/update/{message}', [TicketsController::class, 'messageUpdate'])->name('update');
        Route::delete('/destroy/{message}', [TicketsController::class, 'messageDestroy'])->name('destroy');
        Route::post('/{thread}/', [TicketsController::class, 'messageStore'])->name('store');
    });
});

Route::name('records.')->prefix('records')->middleware(['auth', 'verified'])->group(function () {
    Route::get('/{key}', [RecordsController::class, 'index'])->name('index');
    Route::get('/list/{key}', [RecordsController::class, 'list'])->name('list');
    Route::get('/show-ad/{ad}', [RecordsController::class, 'showAd'])->withTrashed()->name('show-ad');
    Route::get('/show-campaign/{campaign}', [RecordsController::class, 'showCampaign'])->withTrashed()->name('show-campaign');
    Route::get('/show-place/{place}', [RecordsController::class, 'showPlace'])->name('show-place');
});

Route::get('/withdrawal/{data}', [InvoicesPublishersController::class, 'withdrawalRequest'])->name('withdrawal-request');
Route::get('/logs', [LogsController::class, 'index'])->name('logs.index');
Route::get('/logs/logins/list', [LoginAttemptsController::class, 'list'])->name('logs.logins.list');

Route::middleware('web')->get('/', fn() => view('pages.landing'))->name('landing');
Route::middleware('web')->get('publishers', fn() => view('pages.publishers'))->name('publishers');
Route::middleware('web')->get('advertisers', fn() => view('pages.advertisers'))->name('advertisers');
Route::middleware('web')->get('about-us', fn() => view('pages.about-us'))->name('about-us');
Route::middleware('web')->get('frequently-asked-questions', fn() => view('pages.frequently-asked-questions'))->name('frequently-asked-questions');
Route::middleware('web')->get('terms-of-service', fn() => view('pages.terms-of-service'))->name('terms-of-service');
Route::middleware('web')->get('privacy-policy', fn() => view('pages.privacy-policy'))->name('privacy-policy');
Route::middleware('web')->get('refund-policy', fn() => view('pages.refund-policy'))->name('refund-policy');
Route::middleware('web')->get('notice', fn() => view('pages.message'))->name('notice');
Route::middleware('web')->get('cookies-preferences', fn() => view('pages.cookies-preferences'))->name('cookies-preferences');

require __DIR__ . '/auth.php';

require __DIR__ . '/admin.php';

require __DIR__ . '/advertiser.php';

require __DIR__ . '/publisher.php';