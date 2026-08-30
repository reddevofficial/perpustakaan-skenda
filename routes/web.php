<?php

use App\Livewire\Portal\BookDetail;
use App\Livewire\Portal\Catalog;
use App\Livewire\Portal\Login;
use App\Livewire\Portal\MyLoans;
use App\Livewire\Portal\MyReservations;
use App\Livewire\Portal\Notifications;
use App\Livewire\Portal\Profile;
use App\Livewire\Portal\RequestExtension;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::post('/logout', function () {
    Auth::logout();
    session()->invalidate();
    session()->regenerateToken();

    return redirect()->route('portal.catalog');
})->name('logout');

Route::prefix('portal')->name('portal.')->group(function () {
    Route::get('/login', Login::class)->name('login');
    Route::get('/', Catalog::class)->name('catalog');
    Route::get('/book/{id}', BookDetail::class)->name('book');
    Route::get('/loans', MyLoans::class)->name('loans')->middleware('auth');
    Route::get('/loans/{loan_id}/extend', RequestExtension::class)->name('request-extension')->middleware('auth');
    Route::get('/reservations', MyReservations::class)->name('reservations')->middleware('auth');
    Route::get('/notifications', Notifications::class)->name('notifications')->middleware('auth');
    Route::get('/profile', Profile::class)->name('profile')->middleware('auth');
});

Route::name('login')->get('/login', function () {
    return redirect()->route('portal.login');
});
