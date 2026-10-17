<?php

use App\Http\Controllers\AccountNoticeController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\CityController;
use App\Http\Controllers\CityLandingController;
use App\Http\Controllers\CondolenceOverviewController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\CrematoriumController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PublicNoticeWizardController;
use App\Http\Controllers\RegionSubscriptionController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\SubscriptionOverviewController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');
Route::permanentRedirect('/overlijdensbericht-plaatsen', '/bericht-plaatsen');
Route::view('/opties', 'pages.options')->name('options');
Route::get('/contact', [ContactController::class, 'show'])->name('contact');
Route::post('/contact', [ContactController::class, 'send'])->name('contact.send')->middleware(['throttle:5,1', 'honeypot']);
Route::get('/organisaties', [OrganizationController::class, 'index'])->name('organizations.index');
Route::get('/organisatie-toevoegen', [OrganizationController::class, 'create'])->name('organizations.create');
Route::post('/organisatie-toevoegen', [OrganizationController::class, 'store'])->name('organizations.store')->middleware(['throttle:5,1', 'honeypot:website_hp']);
Route::get('/overlijdensbericht/{slug}', [HomeController::class, 'showNotice'])->name('notice.show');
Route::post('/overlijdensbericht/{slug}/condoleances', [HomeController::class, 'storeCondolence'])->name('notice.condolences.store')->middleware(['throttle:6,1', 'honeypot']);
Route::get('/bericht-plaatsen', [PublicNoticeWizardController::class, 'show'])->name('notice.wizard');
Route::post('/bericht-plaatsen', [PublicNoticeWizardController::class, 'submit'])->name('notice.wizard.submit')->middleware(['throttle:10,1', 'honeypot']);
Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/{slug}', [BlogController::class, 'show'])->name('blog.show');
Route::get('/steden', [CityController::class, 'index'])->name('cities.index');
Route::post('/nieuwsbrief/aanmelden', [RegionSubscriptionController::class, 'store'])->name('newsletter.subscribe')->middleware(['throttle:5,1', 'honeypot']);
Route::get('/nieuwsbrief/afmelden/{token}', [RegionSubscriptionController::class, 'destroy'])->name('newsletter.unsubscribe');

Route::middleware('guest')->group(function (): void {
    Route::get('/inloggen', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/inloggen', [AuthController::class, 'login'])->name('login.attempt');
});

Route::middleware('auth')->group(function (): void {
    Route::middleware('admin')->group(function (): void {
        Route::get('/account', [DashboardController::class, 'index'])->name('account.dashboard');
        Route::get('/account/meldingen', [SubscriptionOverviewController::class, 'index'])->name('account.subscriptions');
        Route::get('/account/reacties', [CondolenceOverviewController::class, 'index'])->name('account.condolences');
        Route::delete('/account/reacties/{condolence}', [CondolenceOverviewController::class, 'destroy'])->name('account.condolences.destroy');
        Route::resource('/account/berichten', AccountNoticeController::class)
            ->only(['create', 'store'])
            ->names('account.notices');
    });

    // Eigen berichten beheren; admins mogen alle berichten beheren (zie AccountNoticeController).
    Route::patch('/account/berichten/{notice}/status', [AccountNoticeController::class, 'toggleStatus'])
        ->name('account.notices.status');
    Route::resource('/account/berichten', AccountNoticeController::class)
        ->except(['create', 'store'])
        ->parameters(['berichten' => 'notice'])
        ->names('account.notices');

    Route::post('/uitloggen', [AuthController::class, 'logout'])->name('logout');
});

Route::get('/crematorium', [CrematoriumController::class, 'index'])->name('crematoria.index');
Route::get('/crematorium/{city}', [CrematoriumController::class, 'city'])
    ->where('city', '[A-Za-z0-9\-]+')
    ->name('city.crematorium');

// Oude structuur /{stad}/crematorium blijft werken via een permanente doorverwijzing.
Route::get('/{city}/crematorium', fn (string $city) => redirect()->route('city.crematorium', ['city' => $city], 301))
    ->where('city', '[A-Za-z0-9\-]+');

Route::get('/{city}', [CityLandingController::class, 'city'])
    ->where('city', '[A-Za-z0-9\-]+')
    ->name('city.show');

Route::get('/{path}', [PageController::class, 'show'])
    ->where('path', '.*')
    ->name('pages.show');
