<?php

namespace App\Filament\Resources\ExtracurricularGrade;

use App\Filament\Resources\ExtracurricularGrade\Pages\BatchInputExtracurricularGrade;
use App\Filament\Resources\ExtracurricularGrade\Pages\ManageExtracurricularGrades;
use App\Models\AcademicYear;
use App\Models\ExtracurricularGrade;
use App\Models\Student;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

class ExtracurricularGradeResource extends Resource
{
    protected static ?string $model = ExtracurricularGrade::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-star';

    protected static UnitEnum|string|null $navigationGroup = 'Pengembangan Diri';

    protected static ?int $navigationSort = 5;

    protected static ?string $navigationLabel = 'Nilai Ekskul';

    protected static ?string $modelLabel = 'Nilai Ekskul';

    protected static ?string $pluralModelLabel = 'Nilai Ekskul';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('extracurricular_id')
                ->label('Ekstrakurikuler')
                ->relationship('extracurricular', 'nama_ekskul', modifyQueryUsing: function ($query) {
                    $user = auth()->user();
                    if ($user && !$user->hasAnyRole(['super_admin', 'staff'])) {
                        $query->where('coordinator_user_id', $user->id);
                    }
                })
                ->searchable()
                ->preload()
                ->required()
                ->live()
                ->afterStateUpdated(fn(callable $set) => $set('student_id', null)),

            Select::make('student_id')
                ->label('Siswa')
                ->options(function (callable $get) {
                    $ekstrakurikulerId = $get('extracurricular_id');
                    if (!$ekstrakurikulerId) {
                        return [];
                    }
                    return Student::whereHas('extracurriculars', fn($q) => $q->where('extracurriculars.id', $ekstrakurikulerId))
                        ->with('user')
                        ->get()
                        ->mapWithKeys(fn($s) => [$s->id => "{$s->nisn} - {$s->user->name}"])
                        ->toArray();
                })
                ->searchable()
                ->required()
                ->live(),

            \Filament\Forms\Components\Hidden::make('academic_year_id')
                ->default(fn() => AcademicYear::where('is_active', true)->value('id')),

            Select::make('predikat')
                ->label('Predikat')
                ->options(ExtracurricularGrade::$predikatOptions)
                ->default('B')
                ->required()
                ->live()
                ->afterStateUpdated(function (
                    ?string $state,
                    callable $set,
                    callable $get
                ) {
                    if ($state && !$get('keterangan')) {
                        $set('keterangan', ExtracurricularGrade::$defaultKeterangan[$state] ?? '');
                    }
                }),

            \Filament\Forms\Components\Textarea::make('keterangan')
                ->label('Keterangan')
                ->rows(3)
                ->placeholder('Deskripsi singkat partisipasi dan perkembangan siswa dalam ekskul ini')
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('extracurricular.nama_ekskul')
                    ->label('Ekstrakurikuler')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('student.user.name')
                    ->label('Nama Siswa')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('student.studyGroups.nama_rombel')
                    ->label('Rombel')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('predikat')
                    ->label('Predikat')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'A' => 'success',
                        'B' => 'info',
                        'C' => 'warning',
                        'D' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => ExtracurricularGrade::$predikatOptions[$state] ?? $state),

                TextColumn::make('keterangan')
                    ->label('Keterangan')
                    ->limit(60)
                    ->tooltip(fn($record) => $record->keterangan)
                    ->placeholder('-'),

                TextColumn::make('academicYear.tahun_ajaran')
                    ->label('Tahun Ajaran')
                    ->badge()
                    ->color('primary')
                    ->sortable(),
            ])
            ->defaultSort('extracurricular.nama_ekskul')
            ->filters([
                SelectFilter::make('academic_year_id')
                    ->label('Tahun Ajaran')
                    ->relationship('academicYear', 'tahun_ajaran')
                    ->default(fn() => AcademicYear::where('is_active', true)->value('id')),

                SelectFilter::make('extracurricular_id')
                    ->label('Ekstrakurikuler')
                    ->relationship('extracurricular', 'nama_ekskul', modifyQueryUsing: function ($query) {
                        $user = auth()->user();
                        if ($user && !$user->hasAnyRole(['super_admin', 'staff'])) {
                            $query->where('coordinator_user_id', $user->id);
                        }
                    }),

                SelectFilter::make('study_group_id')
                    ->label('Rombel')
                    ->options(fn() => \App\Models\StudyGroup::pluck('nama_rombel', 'id'))
                    ->query(function ($query, array $data) {
                        if (!empty($data['value'])) {
                            $query->whereHas('student.studyGroups', function ($q) use ($data) {
                                $q->where('study_groups.id', $data['value']);
                            });
                        }
                    }),

                SelectFilter::make('predikat')
                    ->label('Predikat')
                    ->options(ExtracurricularGrade::$predikatOptions),
            ])
            ->actions([
                EditAction::make()
                    ->modalWidth('2xl')
                    ->closeModalByClickingAway(false),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('ViewAny:ExtracurricularGrade') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('Create:ExtracurricularGrade') ?? false;
    }

    public static function canEdit(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return auth()->user()?->can('Update:ExtracurricularGrade') ?? false;
    }

    public static function canDelete(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return auth()->user()?->can('Delete:ExtracurricularGrade') ?? false;
    }

    public static function canDeleteAny(): bool
    {
        return auth()->user()?->can('DeleteAny:ExtracurricularGrade') ?? false;
    }

    public static function canAccess(): bool
    {
        $user = auth()->user();
        if (!$user) return false;

        // Shield permission check (configurable via Filament Shield UI)
        if (!static::canViewAny()) return false;

        // super_admin & staff: full access
        if ($user->hasAnyRole(['super_admin', 'staff'])) return true;

        // guru: hanya koordinator ekskul yang bisa akses
        if ($user->hasRole('guru')) {
            return \App\Models\Extracurricular::where('coordinator_user_id', $user->id)->exists();
        }

        // Role lain yang di-grant via Shield: ikuti Shield permission
        return true;
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $query = parent::getEloquentQuery()
            ->with(['extracurricular', 'student.user', 'student.studyGroups', 'academicYear'])
            ->whereHas('academicYear', fn($q) => $q->where('is_active', true));

        $user = auth()->user();
        if ($user && !$user->hasAnyRole(['super_admin', 'staff'])) {
            $query->whereHas('extracurricular', fn($q) => $q->where('coordinator_user_id', $user->id));
        }

        return $query;
    }

    public static function getPages(): array
    {
        return [
            'index'       => ManageExtracurricularGrades::route('/'),
            'batch-input' => BatchInputExtracurricularGrade::route('/batch-input'),
        ];
    }
}
