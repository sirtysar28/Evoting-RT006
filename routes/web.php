<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DptController;
use App\Http\Controllers\CaptchaController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Halaman Publik (E-Voting Warga)
|--------------------------------------------------------------------------
*/
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/leaderboard', [HomeController::class, 'leaderboard'])->name('leaderboard');
Route::get('/leaderboard/live', [HomeController::class, 'leaderboardLive'])->name('leaderboard.live');
Route::post('/verify-nik', [HomeController::class, 'verifyNik'])->name('verify.nik')->middleware('throttle:20,1');
Route::post('/vote', [HomeController::class, 'castVote'])->name('vote.cast')->middleware('throttle:10,1');
Route::post('/cancel-verification', [HomeController::class, 'cancelVerification'])->name('verify.cancel');

/*
|--------------------------------------------------------------------------
| CAPTCHA
|--------------------------------------------------------------------------
*/
Route::get('/captcha', [CaptchaController::class, 'show'])->name('captcha.show');

/*
|--------------------------------------------------------------------------
| Admin Panitia
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->name('admin.')->group(function () {
    // Guest
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.attempt')->middleware('throttle:10,1');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Terproteksi
    Route::middleware(\App\Http\Middleware\AdminAuth::class)->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/dashboard/live', [DashboardController::class, 'live'])->name('dashboard.live');

        Route::get('/dpt', [DptController::class, 'index'])->name('dpt.index');
        Route::post('/dpt/import', [DptController::class, 'import'])->name('dpt.import');
        Route::get('/dpt/template', [DptController::class, 'template'])->name('dpt.template');
        Route::get('/dpt/export', [DptController::class, 'export'])->name('dpt.export');
        Route::delete('/dpt/{voter}', [DptController::class, 'destroy'])->name('dpt.destroy');
        Route::post('/reset-voting', [DptController::class, 'resetVoting'])->name('voting.reset');

        Route::get('/candidates', [DptController::class, 'candidates'])->name('candidates.index');
        Route::put('/candidates/{candidate}', [DptController::class, 'updateCandidate'])->name('candidates.update');
    });
});
