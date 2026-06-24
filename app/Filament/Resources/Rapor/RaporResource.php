<?php

namespace App\Filament\Resources\Rapor;

use App\Filament\Resources\Rapor\Pages\ListRapors;
use App\Models\Student;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Illuminate\Database\Eloquent\Builder;

class RaporResource extends Resource
{
    protected static ?string $model = Student::class;

    protected static ?string $recordTitleAttribute = 'nisn';

    protected static ?string $slug = 'rapor';

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-document-chart-bar';

    protected static \UnitEnum|string|null $navigationGroup = 'Buku Induk & Rapor';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Rapor Generation';

    protected static ?string $modelLabel = 'Rapor';

    protected static ?string $pluralModelLabel = 'Rapor';

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('ViewAny:Rapor') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('Create:Rapor') ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->can('Update:Rapor') ?? false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->can('Delete:Rapor') ?? false;
    }

    public static function canDeleteAny(): bool
    {
        return auth()->user()?->can('DeleteAny:Rapor') ?? false;
    }

    public static function canAccess(): bool
    {
        $user = auth()->user();
        if (!$user) return false;

        return static::canViewAny();
    }

    public static function getEloquentQuery(): Builder
    {
        $activeYearId = \App\Models\AcademicYear::where('is_active', true)->value('id');
        
        $query = parent::getEloquentQuery()
            ->with(['user', 'studyGroups.level'])
            ->whereHas('studyGroups')
            ->whereHas('studentRapors', function ($q) use ($activeYearId) {
                if ($activeYearId) {
                    $q->where('academic_year_id', $activeYearId);
                }
            });

        $user = auth()->user();

        // Super admin and staff: full access
        if ($user && $user->hasAnyRole(['super_admin', 'staff'])) {
            return $query;
        }

        if ($user && $user->hasRole('guru') && $user->teacher) {
            $teacherId = $user->teacher->id;
            $query->whereHas('studyGroups', function ($q) use ($teacherId) {
                $q->where('walikelas_id', $teacherId)
                  ->orWhereExists(function ($subquery) use ($teacherId) {
                      $subquery->select(\DB::raw(1))
                          ->from('schedules')
                          ->where('schedules.teacher_id', $teacherId)
                          ->whereColumn('schedules.study_group_id', 'study_groups.id');
                  });
            });
        }

        return $query;
    }

    protected static function getGenerateRaporForm(): array
    {
        return [
            \Filament\Forms\Components\Textarea::make('catatan_wali_kelas')
                ->label('Catatan Wali Kelas (AI Generated & Editable)')
                ->rows(4)
                ->required()
                ->hintAction(
                    Action::make('regenerate_ai')
                        ->label('Regenerate via AI')
                        ->icon('heroicon-m-arrow-path')
                        ->color('primary')
                        ->action(function (\Filament\Schemas\Components\Utilities\Set $set, Student $record) {
                            $activeYearId = \App\Models\AcademicYear::where('is_active', true)->value('id');
                            if (!$activeYearId) return;

                            $raporService = new \App\Services\Academic\RaporService();
                            \App\Models\StudentRapor::where('student_id', $record->id)
                                ->where('academic_year_id', $activeYearId)
                                ->delete();

                            $freshRapor = $raporService->generateStudentRapor($record, $activeYearId);
                            $set('catatan_wali_kelas', $freshRapor->catatan_wali_kelas);

                            \Filament\Notifications\Notification::make()
                                ->title('Catatan berhasil diperbarui menggunakan AI')
                                ->success()
                                ->send();
                        })
                ),
            \Filament\Schemas\Components\Grid::make(2)
                ->schema([
                    \Filament\Forms\Components\Toggle::make('is_naik')
                        ->label(fn (Student $record) => $record->studyGroups->first()?->level?->nama_tingkatan && str_contains($record->studyGroups->first()?->level?->nama_tingkatan, '6') ? 'Keterangan Kelulusan (Lulus / Tinggal)' : 'Keterangan Kenaikan Kelas (Naik / Tidak Naik)')
                        ->default(true),
                    \Filament\Forms\Components\TextInput::make('kenaikan_kelas_to')
                        ->label(fn (Student $record) => $record->studyGroups->first()?->level?->nama_tingkatan && str_contains($record->studyGroups->first()?->level?->nama_tingkatan, '6') ? 'Lulus Ke' : 'Naik Ke Kelas')
                        ->placeholder('e.g. II (Dua) / SMP / Sederajat'),
                ])
                ->visible(fn ($get) => (bool)$get('is_genap')),
        ];
    }

    protected static function getCetakRaporForm(): array
    {
        return [
            \Filament\Forms\Components\Select::make('paper_size')
                ->label('Ukuran Kertas')
                ->options([
                    'a4' => 'A4 (210 x 297 mm)',
                    'f4' => 'F4 / Folio (215 x 330 mm)',
                ])
                ->default('a4')
                ->required(),
            \Filament\Forms\Components\Select::make('margin_size')
                ->label('Margin Halaman')
                ->options([
                    'normal' => 'Normal (10mm)',
                    'sedang' => 'Sedang (7mm)',
                    'sempit' => 'Sempit (5mm)',
                    'none' => 'Tanpa Margin (0mm)',
                ])
                ->default('normal')
                ->required(),
        ];
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns(static::getTableColumns())
            ->filters(static::getTableFilters())
            ->actions(static::getTableActions())
            ->bulkActions(static::getTableBulkActions());
    }

    protected static function getTableColumns(): array
    {
        return [
            TextColumn::make('nisn')
                ->label('NISN')
                ->searchable()
                ->sortable(),
            TextColumn::make('user.name')
                ->label('Nama Siswa')
                ->searchable()
                ->sortable(),
            TextColumn::make('studyGroups.nama_rombel')
                ->label('Rombongan Belajar')
                ->badge()
                ->color('info'),
            TextColumn::make('studyGroups.level.nama_tingkatan')
                ->label('Tingkat Kelas')
                ->sortable(),
            TextColumn::make('grades_count')
                ->label('Mapel Dinilai')
                ->counts('grades')
                ->badge()
                ->color(fn ($state) => $state > 0 ? 'success' : 'gray')
                ->sortable(),
            IconColumn::make('studentRaporActive')
                ->label('Status Rapor')
                ->boolean()
                ->trueIcon('heroicon-o-check-circle')
                ->falseIcon('heroicon-o-x-circle')
                ->trueColor('success')
                ->falseColor('gray')
                ->getStateUsing(function (Student $record) {
                    $activeYearId = \App\Models\AcademicYear::where('is_active', true)->value('id');
                    if (!$activeYearId) return false;
                    return \App\Models\StudentRapor::where('student_id', $record->id)
                        ->where('academic_year_id', $activeYearId)
                        ->exists();
                }),
            IconColumn::make('is_rapor_published')
                ->label('Publikasi')
                ->boolean()
                ->trueIcon('heroicon-o-eye')
                ->falseIcon('heroicon-o-eye-slash')
                ->trueColor('success')
                ->falseColor('gray')
                ->getStateUsing(function (Student $record) {
                    $activeYearId = \App\Models\AcademicYear::where('is_active', true)->value('id');
                    if (!$activeYearId) return false;
                    $rapor = \App\Models\StudentRapor::where('student_id', $record->id)
                        ->where('academic_year_id', $activeYearId)
                        ->first();
                    return $rapor ? (bool) $rapor->is_published : false;
                }),
        ];
    }

    protected static function getTableFilters(): array
    {
        return [
            \Filament\Tables\Filters\Filter::make('rombel_filter')
                ->form([
                    \Filament\Forms\Components\Select::make('academic_year_id')
                        ->label('Tahun Ajaran')
                        ->options(fn () => \App\Models\AcademicYear::all()->mapWithKeys(fn ($year) => [
                            $year->id => "Tahun Ajaran {$year->tahun_ajaran} (" . ucfirst($year->semester) . ")"
                        ]))
                        ->default(fn () => \App\Models\AcademicYear::where('is_active', true)->first()?->id)
                        ->live(),
                    \Filament\Forms\Components\Select::make('study_group_id')
                        ->label('Rombel')
                        ->options(function (\Filament\Schemas\Components\Utilities\Get $get) {
                            $academicYearId = $get('academic_year_id');
                            if (!$academicYearId) return \App\Models\StudyGroup::pluck('nama_rombel', 'id');
                            return \App\Models\StudyGroup::where('academic_year_id', $academicYearId)->pluck('nama_rombel', 'id');
                        })
                        ->searchable(),
                ])
                ->query(function (Builder $query, array $data): Builder {
                    return $query
                        ->when(
                            $data['academic_year_id'] ?? null,
                            fn (Builder $query, $value): Builder => $query->whereHas('studyGroups', fn ($q) => $q->where('academic_year_id', $value))
                        )
                        ->when(
                            $data['study_group_id'] ?? null,
                            fn (Builder $query, $value): Builder => $query->whereHas('studyGroups', fn ($q) => $q->where('study_groups.id', $value))
                        );
                })
                ->indicateUsing(function (array $data): array {
                    $indicators = [];
                    if ($data['academic_year_id'] ?? null) {
                        $year = \App\Models\AcademicYear::find($data['academic_year_id']);
                        if ($year) {
                            $indicators[] = \Filament\Tables\Filters\Indicator::make('Tahun Ajaran: ' . $year->tahun_ajaran)
                                ->removeField('academic_year_id');
                        }
                    }
                    if ($data['study_group_id'] ?? null) {
                        $rombel = \App\Models\StudyGroup::find($data['study_group_id']);
                        if ($rombel) {
                            $indicators[] = \Filament\Tables\Filters\Indicator::make('Rombel: ' . $rombel->nama_rombel)
                                ->removeField('study_group_id');
                        }
                    }
                    return $indicators;
                }),
        ];
    }

    protected static function getTableActions(): array
    {
        return [
            Action::make('cetak_rapor')
                ->label('Cetak')
                ->icon('heroicon-o-printer')
                ->color('info')
                ->modalHeading(fn (Student $record) => "Cetak Rapor - {$record->user->name}")
                ->modalWidth('md')
                ->visible(function (Student $record) {
                    $activeYearId = \App\Models\AcademicYear::where('is_active', true)->value('id');
                    if (!$activeYearId) return false;
                    return \App\Models\StudentRapor::where('student_id', $record->id)
                        ->where('academic_year_id', $activeYearId)
                        ->exists();
                })
                ->form(self::getCetakRaporForm())
                ->action(function (Student $record, array $data, \Filament\Resources\Pages\ListRecords $livewire) {
                    $url = route('print.rapor', [
                        'student' => $record->id,
                        'paper_size' => $data['paper_size'],
                        'margin_size' => $data['margin_size'],
                    ]);
                    $livewire->js("window.open('{$url}', '_blank');");
                }),
            Action::make('publish_rapor')
                ->label('Tampilkan ke Ortu & Siswa')
                ->icon('heroicon-o-eye')
                ->color('success')
                ->action(function (Student $record) {
                    $activeYearId = \App\Models\AcademicYear::where('is_active', true)->value('id');
                    if (!$activeYearId) return;

                    $rapor = \App\Models\StudentRapor::where('student_id', $record->id)
                        ->where('academic_year_id', $activeYearId)
                        ->first();
                    
                    if ($rapor) {
                        $rapor->update(['is_published' => true]);
                        \Filament\Notifications\Notification::make()
                            ->title('Rapor berhasil ditampilkan ke orang tua & siswa!')
                            ->success()
                            ->send();
                    }
                })
                ->visible(function (Student $record) {
                    $activeYearId = \App\Models\AcademicYear::where('is_active', true)->value('id');
                    if (!$activeYearId) return false;
                    $rapor = \App\Models\StudentRapor::where('student_id', $record->id)
                        ->where('academic_year_id', $activeYearId)
                        ->first();
                    return $rapor && !$rapor->is_published;
                }),
            Action::make('unpublish_rapor')
                ->label('Sembunyikan dari Ortu & Siswa')
                ->icon('heroicon-o-eye-slash')
                ->color('danger')
                ->requiresConfirmation()
                ->action(function (Student $record) {
                    $activeYearId = \App\Models\AcademicYear::where('is_active', true)->value('id');
                    if (!$activeYearId) return;

                    $rapor = \App\Models\StudentRapor::where('student_id', $record->id)
                        ->where('academic_year_id', $activeYearId)
                        ->first();
                    
                    if ($rapor) {
                        $rapor->update(['is_published' => false]);
                        \Filament\Notifications\Notification::make()
                            ->title('Rapor berhasil disembunyikan!')
                            ->warning()
                            ->send();
                    }
                })
                ->visible(function (Student $record) {
                    $activeYearId = \App\Models\AcademicYear::where('is_active', true)->value('id');
                    if (!$activeYearId) return false;
                    $rapor = \App\Models\StudentRapor::where('student_id', $record->id)
                        ->where('academic_year_id', $activeYearId)
                        ->first();
                    return $rapor && $rapor->is_published;
                }),
        ];
    }

    protected static function getTableBulkActions(): array
    {
        return [
            \Filament\Actions\BulkActionGroup::make([
                BulkAction::make('publish_selected')
                    ->label('Tampilkan ke Ortu & Siswa')
                    ->icon('heroicon-o-eye')
                    ->color('success')
                    ->action(function (\Illuminate\Database\Eloquent\Collection $records) {
                        $activeYearId = \App\Models\AcademicYear::where('is_active', true)->value('id');
                        if (!$activeYearId) return;

                        $count = 0;
                        foreach ($records as $record) {
                            $rapor = \App\Models\StudentRapor::where('student_id', $record->id)
                                ->where('academic_year_id', $activeYearId)
                                ->first();
                            if ($rapor) {
                                $rapor->update(['is_published' => true]);
                                $count++;
                            }
                        }

                        if ($count > 0) {
                            \Filament\Notifications\Notification::make()
                                ->title("Rapor untuk {$count} siswa berhasil ditampilkan ke orang tua & siswa!")
                                ->success()
                                ->send();
                        } else {
                            \Filament\Notifications\Notification::make()
                                ->title("Tidak ada rapor yang sudah digenerate dari siswa terpilih.")
                                ->warning()
                                ->send();
                        }
                    }),
                BulkAction::make('unpublish_selected')
                    ->label('Sembunyikan dari Ortu & Siswa')
                    ->icon('heroicon-o-eye-slash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function (\Illuminate\Database\Eloquent\Collection $records) {
                        $activeYearId = \App\Models\AcademicYear::where('is_active', true)->value('id');
                        if (!$activeYearId) return;

                        $count = 0;
                        foreach ($records as $record) {
                            $rapor = \App\Models\StudentRapor::where('student_id', $record->id)
                                ->where('academic_year_id', $activeYearId)
                                ->first();
                            if ($rapor) {
                                $rapor->update(['is_published' => false]);
                                $count++;
                            }
                        }

                        if ($count > 0) {
                            \Filament\Notifications\Notification::make()
                                ->title("Rapor untuk {$count} siswa berhasil disembunyikan!")
                                ->warning()
                                ->send();
                        }
                    }),
                \Filament\Actions\DeleteBulkAction::make(),
            ]),
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRapors::route('/'),
        ];
    }
}
