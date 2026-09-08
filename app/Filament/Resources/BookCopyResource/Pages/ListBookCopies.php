<?php

namespace App\Filament\Resources\BookCopyResource\Pages;

use App\Filament\Resources\BookCopyResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListBookCopies extends ListRecords
{
    protected static string $resource = BookCopyResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label('Tambah eksemplar')];
    }
}
