<?php

namespace App\Filament\Resources\Extracurriculars\RelationManagers;

use App\Models\AcademicYear;
use App\Models\Student;
use Filament\Actions\Action;
use Filament\Actions\AttachAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ExtracurricularStudentRelationManager extends RelationManager
{
    protected static string $relationship = 'students';

    protected static ?string $title = 'Anggota';

    protected static ?string $recordTitleAttribute = 'id';

    public function table(Table $table): Table
    {
        $ekskul = $this->getOwnerRecord();
        $isWajib = $ekskul->kategori === 'wajib';

        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('Nama Siswa')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('studyGroups.nama_rombel')
                    ->label('Rombel')
                    ->badge()
                    ->searchable(),
            ])
            ->headerActions(
                $isWajib
                    ? [
                        Action::make('syncAllStudents')
                            ->label('Sinkronkan Semua Siswa')
                            ->icon('heroicon-o-arrow-path')
                            ->color('warning')
                            ->requiresConfirmation()
                            ->modalHeading('Sinkronkan Semua Siswa')
                            ->modalDescription('Semua siswa yang terdaftar di rombel tahun ajaran aktif akan otomatis menjadi anggota ekskul wajib ini. Lanjutkan?')
                            ->action(function () {
                                $ekskul = $this->getOwnerRecord();
                                $activeYearId = AcademicYear::where('is_active', true)->value('id');

                                if (! $activeYearId) {
                                    Notification::make()
                                        ->title('Gagal')
                                        ->body('Tidak ada tahun ajaran aktif.')
                                        ->danger()
                                        ->send();

                                    return;
                                }

                                // Get all student IDs from all study groups in active academic year
                                $studentIds = Student::whereHas('studyGroups', function ($q) use ($activeYearId) {
                                    $q->where('academic_year_id', $activeYearId);
                                })->pluck('id')->toArray();

                                $ekskul->students()->syncWithoutDetaching($studentIds);

                                Notification::make()
                                    ->title('Berhasil')
                                    ->body('Berhasil menyinkronkan '.count($studentIds).' siswa ke ekskul ini.')
                                    ->success()
                                    ->send();
                            }),
                    ]
                    : [
                        AttachAction::make()
                            ->label('Tambah Anggota')
                            ->preloadRecordSelect()
                            ->recordSelectOptionsQuery(function ($query) {
                                return $query->with('user')
                                    ->join('users', 'students.user_id', '=', 'users.id')
                                    ->select('students.*')
                                    ->whereHas('studyGroups', function ($q) {
                                        $activeYearId = AcademicYear::where('is_active', true)->value('id');
                                        if ($activeYearId) {
                                            $q->where('academic_year_id', $activeYearId);
                                        }
                                    });
                            })
                            ->recordSelectSearchColumns(['users.name'])
                            ->recordTitle(fn ($record) => $record->user?->name ?? 'Siswa #'.$record->id),
                    ]
            )
            ->actions([
                DetachAction::make()
                    ->label('Hapus'),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DetachBulkAction::make(),
                ]),
            ]);
    }

    protected function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['user', 'studyGroups']);
    }
}
