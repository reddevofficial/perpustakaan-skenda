<?php

namespace App\Filament\Resources\BookCopyResource\Pages;

use App\Filament\Resources\BookCopyResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBookCopy extends EditRecord
{
    protected static string $resource = BookCopyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
