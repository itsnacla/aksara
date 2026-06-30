<?php

namespace App\Filament\Resources\PelengkapRapor;

use App\Filament\Resources\PelengkapRapor\Pages\ListPelengkapRapor;
use App\Models\AcademicYear;
use App\Models\Student;
use App\Models\StudyGroup;
use App\Services\Academic\BukuIndukService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PelengkapRaporResource extends Resource
{
    protected static ?string $model = Student::class;

    protected static ?string $recordTitleAttribute = 'nisn';

    protected static ?string $slug = 'pelengkap-rapor';

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-book-open';

    protected static \UnitEnum|string|null $navigationGroup = 'Buku Induk & Rapor';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Pelengkap Rapor';

    protected static ?string $modelLabel = 'Pelengkap Rapor';

    protected static ?string $pluralModelLabel = 'Pelengkap Rapor';

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
            ->withoutGlobalScope(\App\Models\Scopes\ActiveScope::class)
            ->with(['user', 'studyGroups.level']);

        $user = auth()->user();

        // Super admin and staff: full access
        if ($user && $user->hasAnyRole(['super_admin', 'staff'])) {
            return $query;
        }

        // Guru (Wali Kelas / Mapel)
        if ($user && $user->hasRole('guru') && $user->teacher) {
            $teacherId = $user->teacher->id;

            return $query->whereHas('studyGroups', function ($q) use ($teacherId) {
                $q->where('walikelas_id', $teacherId)
                    ->orWhereExists(function ($subquery) use ($teacherId) {
                        $subquery->select(\DB::raw(1))
                            ->from('schedules')
                            ->where('schedules.teacher_id', $teacherId)
                            ->whereColumn('schedules.study_group_id', 'study_groups.id');
                    });
            });
        }

        // Default: no access
        return $query->whereRaw('1 = 0');
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nisn')
                    ->label('NISN')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('nis')
                    ->label('NIS')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('user.name')
                    ->label('Nama Siswa')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('gender')
                    ->label('JK')
                    ->formatStateUsing(fn ($state) => $state === 'L' ? 'Laki-laki' : ($state === 'P' ? 'Perempuan' : '-'))
                    ->sortable(),
                TextColumn::make('studyGroups.nama_rombel')
                    ->label('Rombongan Belajar')
                    ->badge()
                    ->color('success'),
                IconColumn::make('is_buku_induk_generated')
                    ->label('Status Buku Induk')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('gray'),
            ])
            ->filters([
                SelectFilter::make('academic_year')
                    ->label('Tahun Ajaran')
                    ->options(
                        fn () => AcademicYear::query()
                            ->get()
                            ->mapWithKeys(fn ($year) => [$year->id => "{$year->tahun_ajaran} - ".ucfirst($year->semester)])
                    )
                    ->query(function ($query, array $data) {
                        if (empty($data['value'])) {
                            return $query;
                        }

                        return $query->whereHas('studyGroups', function ($q) use ($data) {
                            $q->where('study_groups.academic_year_id', $data['value']);
                        });
                    })
                    ->default(fn () => AcademicYear::where('is_active', true)->first()?->id),
                SelectFilter::make('studyGroups')
                    ->label('Filter Rombel')
                    ->relationship('studyGroups', 'nama_rombel', function ($query, $livewire) {
                        $academicYearId = $livewire->tableFilters['academic_year']['value'] ?? null;
                        $academicYearId = $academicYearId ?: AcademicYear::where('is_active', true)->first()?->id;
                        if ($academicYearId) {
                            return $query->where('academic_year_id', $academicYearId);
                        }

                        return $query;
                    })
                    ->default(fn () => StudyGroup::whereHas('academicYear', fn ($q) => $q->where('is_active', true))
                        ->whereHas('level', fn ($q) => $q->where('nama_tingkatan', 'like', '%Kelas 1%'))
                        ->first()?->id),
            ])
            ->actions([
                Action::make('generate_buku_induk')
                    ->label('Generate')
                    ->icon('heroicon-o-cpu-chip')
                    ->color('success')
                    ->action(function (Student $record) {
                        $activeYearId = AcademicYear::where('is_active', true)->value('id');
                        if (! $activeYearId) {
                            Notification::make()
                                ->title('Tahun ajaran aktif tidak ditemukan')
                                ->danger()
                                ->send();

                            return;
                        }

                        $bukuIndukService = new BukuIndukService;
                        $bukuIndukService->generateStudentBukuInduk($record, $activeYearId);

                        Notification::make()
                            ->title('Buku Induk berhasil digenerate!')
                            ->success()
                            ->send();
                    })
                    ->visible(fn (Student $record) => ! $record->is_buku_induk_generated),
                Action::make('cetak_pelengkap')
                    ->label('Cetak Pelengkap')
                    ->icon('heroicon-o-printer')
                    ->color('info')
                    ->url(fn (Student $record): string => route('print.pelengkap-rapor', $record))
                    ->openUrlInNewTab()
                    ->visible(fn (Student $record) => (bool) $record->is_buku_induk_generated),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPelengkapRapor::route('/'),
        ];
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('ViewAny:PelengkapRapor') ?? false;
    }

    public static function canCreate(): bool
    {
        return false; // Pelengkap Rapor tidak bisa dibuat manual
    }

    public static function canEdit($record): bool
    {
        return false; // Pelengkap Rapor tidak bisa diedit langsung
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->hasAnyRole(['super_admin', 'staff']) ?? false;
    }

    public static function canDeleteAny(): bool
    {
        return auth()->user()?->hasAnyRole(['super_admin', 'staff']) ?? false;
    }
}
