<?php

namespace App\Filament\Resources;

use App\Filament\Resources\P5ProjectResource\Pages;
use App\Filament\Resources\P5ProjectResource\Schemas\P5ProjectForm;
use App\Filament\Resources\P5ProjectResource\Tables\P5ProjectTable;
use App\Models\P5Project;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class P5ProjectResource extends Resource
{
    protected static ?string $model = P5Project::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-document-text';

    protected static \UnitEnum|string|null $navigationGroup = 'Pengembangan Diri & P5';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Kegiatan Kokurikuler';

    protected static ?string $modelLabel = 'Kegiatan Kokurikuler';

    protected static ?string $pluralModelLabel = 'Kegiatan Kokurikuler';

    public static function form(Schema $schema): Schema
    {
        return P5ProjectForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return P5ProjectTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageP5Projects::route('/'),
        ];
    }
}
