<?php

use App\Enums\UserRole;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ExtensionController;
use App\Http\Controllers\LoanController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\BookLabelController;
use App\Livewire\Portal\BookDetail;
use App\Livewire\Portal\Catalog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::view('/access-denied', 'errors.access-denied')->name('access.denied');

Route::get('/katalog', Catalog::class)->name('portal.catalog');
Route::get('/katalog/{book}', BookDetail::class)->name('portal.book');

Route::middleware('auth')->group(function () {
    Route::post('/buku/{book}/peminjaman', [LoanController::class, 'store'])->name('loans.store');
    Route::get('/peminjaman-saya', [LoanController::class, 'mine'])->name('loans.mine');
    Route::get('/staff/barcode', \App\Livewire\Staff\BarcodeScanner::class)->name('staff.barcode');
    Route::post('/peminjaman/{loan}/setujui', [LoanController::class, 'approve'])->name('loans.approve');
    Route::post('/peminjaman/{loan}/tolak', [LoanController::class, 'reject'])->name('loans.reject');
    Route::post('/peminjaman/{loan}/serahkan', [LoanController::class, 'handover'])->name('loans.handover');
    Route::post('/peminjaman/{loan}/kembalikan', [LoanController::class, 'return'])->name('loans.return');
    Route::post('/peminjaman/{loan}/perpanjangan', [ExtensionController::class, 'store'])->name('extensions.store');
    Route::post('/perpanjangan/{extension}/setujui', [ExtensionController::class, 'approve'])->name('extensions.approve');
    Route::post('/perpanjangan/{extension}/tolak', [ExtensionController::class, 'reject'])->name('extensions.reject');
    Route::post('/buku/{book}/reservasi', [ReservationController::class, 'store'])->name('reservations.store');
    Route::post('/reservasi/{reservation}/batal', [ReservationController::class, 'cancel'])->name('reservations.cancel');
    Route::get('/staff/inventaris', [InventoryController::class, 'index'])->name('inventory.index');
    Route::post('/staff/inventaris', [InventoryController::class, 'update'])->name('inventory.update');
    Route::get('/staff/laporan', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/staff/laporan/export', [ReportController::class, 'export'])->name('reports.export');
    Route::get('/staff/label-buku', [BookLabelController::class, 'index'])->name('book-labels.index');

    Route::get('/redirect-dashboard', function () {
        $user = Auth::user();

        if (! $user) {
            return redirect()->route('filament.admin.auth.login');
        }

        return match ($user->role) {
            UserRole::SUPER_ADMIN => redirect()->to('/admin'),
            UserRole::STAFF => redirect()->to('/staff'),
            UserRole::STUDENT => redirect()->to('/siswa'),
            default => redirect()->to('/access-denied'),
        };
    })->name('dashboard.redirect');
});
