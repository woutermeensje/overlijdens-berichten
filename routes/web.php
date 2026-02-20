<?php

use App\Http\Controllers\AccountNoticeController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PageController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/overlijdensbericht-plaatsen', [HomeController::class, 'placeNotice'])->name('notice.place');

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

Route::get('/{path}', [PageController::class, 'show'])
    ->where('path', '.*')
    ->name('pages.show');
