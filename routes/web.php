<?php

use App\Http\Controllers\AccountNoticeController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\CityController;
use App\Http\Controllers\CityLandingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PublicNoticeWizardController;
use App\Http\Controllers\RegionSubscriptionController;
use App\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');
Route::get('/overlijdensbericht-plaatsen', [HomeController::class, 'placeNotice'])->name('notice.place');
Route::get('/overlijdensbericht/{slug}', [HomeController::class, 'showNotice'])->name('notice.show');
Route::get('/bericht-plaatsen', [PublicNoticeWizardController::class, 'show'])->name('notice.wizard');
Route::post('/bericht-plaatsen', [PublicNoticeWizardController::class, 'submit'])->name('notice.wizard.submit');
Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/{slug}', [BlogController::class, 'show'])->name('blog.show');
Route::get('/steden', [CityController::class, 'index'])->name('cities.index');
Route::post('/nieuwsbrief/aanmelden', [RegionSubscriptionController::class, 'store'])->name('newsletter.subscribe');
Route::get('/nieuwsbrief/afmelden/{token}', [RegionSubscriptionController::class, 'destroy'])->name('newsletter.unsubscribe');

Route::middleware('guest')->group(function (): void {
    Route::get('/inloggen', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/inloggen', [AuthController::class, 'login'])->name('login.attempt');

    Route::get('/account-aanmaken', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/account-aanmaken', [AuthController::class, 'register'])->name('register.store');
});

Route::middleware('auth')->group(function (): void {
    Route::get('/account', [DashboardController::class, 'index'])->name('account.dashboard');
    Route::resource('/account/berichten', AccountNoticeController::class)
        ->parameters(['berichten' => 'notice'])
        ->names('account.notices');

    Route::post('/uitloggen', [AuthController::class, 'logout'])->name('logout');
});

Route::get('/{city}/crematorium', [CityLandingController::class, 'crematorium'])
    ->where('city', '[A-Za-z0-9\-]+')
    ->name('city.crematorium');

Route::get('/{city}', [CityLandingController::class, 'city'])
    ->where('city', '[A-Za-z0-9\-]+')
    ->name('city.show');

Route::get('/{path}', [PageController::class, 'show'])
    ->where('path', '.*')
    ->name('pages.show');
