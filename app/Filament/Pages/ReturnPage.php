<?php

namespace App\Filament\Pages;

use App\Models\Loan;
use App\Models\LoanItem;
use App\Services\ReturnService;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class ReturnPage extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-arrow-uturn-left';

    protected static string|\UnitEnum|null $navigationGroup = 'Transaksi';

    protected static ?string $navigationLabel = 'Pengembalian';

    protected static ?int $navigationSort = 1;

    protected string $view = 'filament.pages.return-page';

    public ?string $barcode = null;

    public ?Loan $foundLoan = null;

    public ?LoanItem $foundItem = null;

    public function lookupBarcode(): void
    {
        $this->foundLoan = null;
        $this->foundItem = null;

        if (blank($this->barcode)) {
            return;
        }

        $item = LoanItem::with(['loan.student', 'bookCopy.book'])
            ->whereHas('bookCopy', fn ($q) => $q->where('barcode', $this->barcode))
            ->where('status', '!=', 'returned')
            ->first();

        if ($item) {
            $this->foundItem = $item;
            $this->foundLoan = $item->loan;
        }
    }

    public function processReturn(): void
    {
        $this->lookupBarcode();

        if (! $this->foundItem) {
            Notification::make()->title('Tidak ada peminjaman aktif ditemukan')->danger()->send();

            return;
        }

        try {
            app(ReturnService::class)->returnItem($this->foundItem);

            Notification::make()
                ->title('Pengembalian berhasil')
                ->body("Buku {$this->foundItem->bookCopy->barcode} telah dikembalikan.")
                ->success()
                ->send();

            $this->foundLoan = null;
            $this->foundItem = null;
            $this->barcode = null;
        } catch (\Exception $e) {
            Notification::make()
                ->title('Gagal memproses pengembalian')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
}
