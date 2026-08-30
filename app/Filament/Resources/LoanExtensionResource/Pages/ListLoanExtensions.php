<?php

namespace App\Filament\Resources\LoanExtensionResource\Pages;

use App\Filament\Resources\LoanExtensionResource;
use Filament\Resources\Pages\ListRecords;

class ListLoanExtensions extends ListRecords
{
    protected static string $resource = LoanExtensionResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
