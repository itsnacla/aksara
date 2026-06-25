<?php

namespace App\Filament\Resources\P5Theme;

use App\Filament\Resources\P5Theme\Schemas\P5ThemeForm;
use App\Filament\Resources\P5Theme\Tables\P5ThemeTable;
use App\Models\AcademicYear;
use App\Models\P5Theme;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class P5ThemeResource extends Resource
{
    protected static ?string $model = P5Theme::class;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $slug = 'p5-theme';

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-swatch';

    protected static \UnitEnum|string|null $navigationGroup = 'Pengembangan Diri';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Tema Kokurikuler';

    protected static ?string $modelLabel = 'Tema Kokurikuler';

    protected static ?string $pluralModelLabel = 'Tema Kokurikuler';

    public static function form(Schema $schema): Schema
    {
        return P5ThemeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return P5ThemeTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        // Filter by active academic year
        $activeYearId = AcademicYear::where('is_active', true)->value('id');
        if ($activeYearId) {
            $query->where('academic_year_id', $activeYearId);
        }

        return $query;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageP5Themes::route('/'),
        ];
    }
}
