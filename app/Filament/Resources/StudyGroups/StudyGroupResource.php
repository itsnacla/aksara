<?php

namespace App\Filament\Resources\StudyGroups;

use App\Filament\Resources\StudyGroups\Pages\CreateStudyGroup;
use App\Filament\Resources\StudyGroups\Pages\EditStudyGroup;
use App\Filament\Resources\StudyGroups\Pages\ListStudyGroups;
use App\Filament\Resources\StudyGroups\Schemas\StudyGroupForm;
use App\Filament\Resources\StudyGroups\Tables\StudyGroupsTable;
use App\Models\StudyGroup;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;
use Filament\Support\Icons\Heroicon;

class StudyGroupResource extends Resource
{
    protected static ?string $model = StudyGroup::class;

    protected static ?string $recordTitleAttribute = 'nama_rombel';

    protected static ?string $slug = 'rombel';

    protected static ?string $modelLabel = 'Rombel';

    protected static ?string $pluralModelLabel = 'Rombel';

    protected static ?string $navigationLabel = 'Rombel / Kelas';

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-user-group';

    protected static UnitEnum|string|null $navigationGroup = 'Master Data';

    protected static ?int $navigationSort = 5;

    public static function form(Schema $schema): Schema
    {
        return StudyGroupForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return StudyGroupsTable::configure($table);
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $query = parent::getEloquentQuery()->with(['academicYear', 'level', 'classroom', 'walikelas.user']);
        $user = auth()->user();

        if ($user->hasRole('guru')) {
            $query->where('walikelas_id', $user->teacher->id ?? 0);
        }

        return $query;
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\StudentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListStudyGroups::route('/'),
            'create' => CreateStudyGroup::route('/create'),
            'view' => Pages\ViewStudyGroup::route('/{record}'),
            'edit' => EditStudyGroup::route('/{record}/edit'),
        ];
    }
}
