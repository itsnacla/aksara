<?php

namespace App\Filament\Resources\AcademicYears\Tables;

use App\Models\AcademicYear;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Table;
use Filament\Notifications\Notification;

class AcademicYearsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->headerActions([
                Action::make('sync_automatic')
                    ->label('Sync Otomatis (Sesuai Kalender)')
                    ->icon('heroicon-o-arrow-path')
                    ->color('info')
                    ->requiresConfirmation()
                    ->modalHeading('Sinkronisasi Tahun Ajaran')
                    ->modalDescription('Sistem akan mendeteksi tahun ajaran dan semester yang aktif berdasarkan tanggal hari ini.')
                    ->action(function ($livewire) {
                        $month = (int) date('n');
                        $year = (int) date('Y');

                        if ($month >= 7) {
                            $targetTahun = $year . '/' . ($year + 1);
                            $targetSemester = 'ganjil';
                        } else {
                            $targetTahun = ($year - 1) . '/' . $year;
                            $targetSemester = 'genap';
                        }

                        $academicYear = AcademicYear::firstOrCreate(
                            ['tahun_ajaran' => $targetTahun],
                            ['semester' => $targetSemester]
                        );

                        // If it already existed, we still want to ensure the semester is synced to the calendar if that's what's requested
                        $academicYear->update([
                            'semester' => $targetSemester,
                            'is_active' => true
                        ]);

                        Notification::make()
                            ->title('Sinkronisasi Berhasil!')
                            ->success()
                            ->body("Tahun Ajaran {$targetTahun} ({$targetSemester}) sekarang menjadi aktif.")
                            ->send();
                            
                        $livewire->dispatch('active-academic-year-changed');
                    })
            ])
            ->columns([
                TextColumn::make('tahun_ajaran')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('semester')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'ganjil' => 'warning',
                        'genap' => 'success',
                    }),
                IconColumn::make('is_active')
                    ->label('Status Aktif')
                    ->boolean(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Action::make('activate')
                    ->label('Aktifkan Tahun')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->link()
                    ->hidden(fn ($record) => $record->is_active)
                    ->action(function ($record, $livewire) {
                        $record->update(['is_active' => true]);
                        
                        Notification::make()
                            ->title('Tahun Ajaran Diaktifkan')
                            ->success()
                            ->send();
                            
                        $livewire->dispatch('active-academic-year-changed');
                    }),
                Action::make('switch_semester')
                    ->label(fn ($record) => $record->semester === 'ganjil' ? 'Pindah ke Genap' : 'Pindah ke Ganjil')
                    ->icon('heroicon-o-arrows-right-left')
                    ->color('warning')
                    ->link()
                    ->visible(fn ($record) => $record->is_active)
                    ->requiresConfirmation()
                    ->action(function ($record, $livewire) {
                        $newSemester = $record->semester === 'ganjil' ? 'genap' : 'ganjil';
                        $record->update([
                            'semester' => $newSemester,
                            'rapor_date' => null,
                            'schedule_date' => null,
                            'attendance_date' => null,
                            'pelengkap_rapor_date' => null,
                        ]);
                        
                        Notification::make()
                            ->title('Semester Berhasil Dipindah')
                            ->success()
                            ->body("Sekarang berada di semester " . ucfirst($newSemester))
                            ->send();
                            
                        $livewire->dispatch('active-academic-year-changed');
                    }),
                ViewAction::make()->modal(),
                EditAction::make()->modal(),
                DeleteAction::make()
                    ->modal()
                    ->before(function (DeleteAction $action, AcademicYear $record) {
                        // Check if academic year has related records
                        $hasGrades = $record->grades()->exists();
                        $hasReports = $record->eReports()->exists();
                        $hasSchedules = $record->schedules()->exists();
                        $hasClassrooms = $record->studyGroups()->exists();

                        if ($hasGrades || $hasReports || $hasSchedules || $hasClassrooms) {
                            $relatedItems = [];
                            if ($hasGrades) $relatedItems[] = 'Nilai';
                            if ($hasReports) $relatedItems[] = 'Rapor';
                            if ($hasSchedules) $relatedItems[] = 'Jadwal';
                            if ($hasClassrooms) $relatedItems[] = 'Kelas';

                            Notification::make()
                                ->title('Tidak Dapat Menghapus Tahun Ajaran')
                                ->danger()
                                ->body('Tahun ajaran ini masih memiliki data terkait: ' . implode(', ', $relatedItems) . '. Hapus data terkait terlebih dahulu.')
                                ->persistent()
                                ->send();

                            $action->cancel();
                        }
                    }),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
