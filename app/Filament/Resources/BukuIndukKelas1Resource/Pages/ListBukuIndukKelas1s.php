<?php

namespace App\Filament\Resources\BukuIndukKelas1Resource\Pages;

use App\Filament\Resources\BukuIndukKelas1Resource;
use Filament\Resources\Pages\ListRecords;

class ListBukuIndukKelas1s extends ListRecords
{
    protected static string $resource = BukuIndukKelas1Resource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Purely read/print page
        ];
    }
}
