<?php

namespace App\Filament\Resources\Cocurriculars;

use App\Filament\Resources\Cocurriculars\Pages\ListCocurriculars;
use App\Filament\Resources\Cocurriculars\Schemas\CocurricularForm;
use App\Filament\Resources\Cocurriculars\Tables\CocurricularTable;
use App\Models\Cocurricular;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class CocurricularResource extends Resource
{
    protected static ?string $model = Cocurricular::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-puzzle-piece';

    protected static \UnitEnum|string|null $navigationGroup = 'Manajemen Akademik';

    protected static ?int $navigationSort = 5;

    protected static ?string $navigationLabel = 'Kokurikuler (P5)';

    protected static ?string $pluralLabel = 'Kokurikuler (P5)';

    public static function form(Schema $schema): Schema
    {
        return CocurricularForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CocurricularTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCocurriculars::route('/'),
        ];
    }
}
