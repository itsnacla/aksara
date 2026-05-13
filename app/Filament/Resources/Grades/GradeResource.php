<?php

namespace App\Filament\Resources\Grades;

use App\Filament\Resources\Grades\Pages\ManageGrades;
use App\Models\Grade;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class GradeResource extends Resource
{
    protected static ?string $model = Grade::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-academic-cap';

    protected static UnitEnum|string|null $navigationGroup = 'Manajemen Akademik';

    protected static ?int $navigationSort = 5;

    protected static ?string $navigationLabel = 'Nilai Siswa';

    protected static ?string $label = 'Nilai';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('student_id')
                ->relationship('student', 'nisn', fn ($query) => $query->with('user'))
                ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->nisn} - {$record->user->name}")
                ->searchable()
                ->required(),
            Select::make('subject_id')
                ->relationship('subject', 'nama_mapel')
                ->required(),
            Select::make('study_group_id')
                ->relationship('studyGroup', 'nama_rombel')
                ->required(),
            Select::make('academic_year_id')
                ->relationship('academicYear', 'tahun_ajaran', fn ($query) => $query->where('is_active', true))
                ->default(fn () => \App\Models\AcademicYear::where('is_active', true)->first()?->id)
                ->required(),
            \Filament\Schemas\Components\Grid::make(3)
                ->schema([
                    TextInput::make('nilai_tugas')->numeric()->minValue(0)->maxValue(100)->required(),
                    TextInput::make('nilai_uts')->numeric()->minValue(0)->maxValue(100)->required(),
                    TextInput::make('nilai_uas')->numeric()->minValue(0)->maxValue(100)->required(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('student.user.name')
                    ->label('Nama Siswa')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('studyGroup.nama_rombel')
                    ->label('Rombel')
                    ->sortable(),
                TextColumn::make('subject.nama_mapel')
                    ->label('Mapel')
                    ->sortable(),
                TextColumn::make('nilai_tugas')
                    ->label('Tugas')
                    ->numeric(),
                TextColumn::make('nilai_uts')
                    ->label('UTS')
                    ->numeric(),
                TextColumn::make('nilai_uas')
                    ->label('UAS')
                    ->numeric(),
                TextColumn::make('total_nilai')
                    ->label('Rata-rata')
                    ->state(fn ($record) => round(($record->nilai_tugas + $record->nilai_uts + $record->nilai_uas) / 3, 2))
                    ->badge()
                    ->color(fn ($state) => $state >= 75 ? 'success' : ($state >= 60 ? 'warning' : 'danger')),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('academic_year_id')
                    ->label('Tahun Ajaran')
                    ->relationship('academicYear', 'tahun_ajaran')
                    ->default(fn () => \App\Models\AcademicYear::where('is_active', true)->first()?->id),
                \Filament\Tables\Filters\SelectFilter::make('study_group_id')
                    ->label('Rombel')
                    ->relationship('studyGroup', 'nama_rombel'),
            ])
            ->actions([
                EditAction::make()->modal(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();

        if ($user && $user->hasRole('guru') && $user->teacher) {
            $teacherId = $user->teacher->id;
            
            $query->where(function ($q) use ($teacherId) {
                // Guru Mapel: Only see their subjects/rombel from Schedule
                $q->whereHas('subject', function ($sq) use ($teacherId) {
                    $sq->whereHas('schedules', fn ($ssq) => $ssq->where('teacher_id', $teacherId));
                })
                // Wali Kelas: See all grades for their Rombel
                ->orWhereHas('studyGroup', fn ($sq) => $sq->where('walikelas_id', $teacherId));
            });
        }

        return $query;
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageGrades::route('/'),
        ];
    }
}
