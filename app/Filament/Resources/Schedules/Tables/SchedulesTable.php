<?php

namespace App\Filament\Resources\Schedules\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\DeleteAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;

class SchedulesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('hari')
                    ->sortable()
                    ->badge()
                    ->color('info'),
                
                TextColumn::make('startTimeSlot.nama_jam')
                    ->label('Dari')
                    ->description(fn ($record) => $record->startTimeSlot?->waktu_mulai?->format('H:i')),
                
                TextColumn::make('endTimeSlot.nama_jam')
                    ->label('Sampai')
                    ->description(fn ($record) => $record->endTimeSlot?->waktu_selesai?->format('H:i')),

                TextColumn::make('subject.nama_mapel')
                    ->label('Mata Pelajaran')
                    ->description(fn ($record) => $record->subject?->kode_mapel ? "Kode: {$record->subject->kode_mapel}" : null)
                    ->searchable()
                    ->sortable(),

                TextColumn::make('teacher.nama_lengkap')
                    ->label('Guru / Pengajar')
                    ->searchable(['user.name'])
                    ->description(fn ($record) => $record->teacher?->kode_guru ? "Kode: {$record->teacher->kode_guru}" : null)
                    ->searchable(false)
                    ->sortable(false)
                    ->formatStateUsing(fn ($record) => $record->teacher?->nama_lengkap ?? '-'),

                TextColumn::make('studyGroup.nama_rombel')
                    ->label('Rombel')
                    ->badge()
                    ->searchable()
                    ->sortable(),
                
                TextColumn::make('studyGroup.classroom.nama_ruangan')
                    ->label('Ruangan')
                    ->toggleable(),
            ])
            ->filters([
                \Filament\Tables\Filters\Filter::make('rombel_filter')
                    ->form([
                        \Filament\Forms\Components\Select::make('academic_year_id')
                            ->label('Tahun Ajaran')
                            ->options(fn () => \App\Models\AcademicYear::all()->mapWithKeys(fn ($year) => [
                                $year->id => "Tahun Ajaran {$year->tahun_ajaran}"
                            ]))
                            ->default(fn () => \App\Models\AcademicYear::where('is_active', true)->first()?->id)
                            ->live(),
                        \Filament\Forms\Components\Select::make('study_group_id')
                            ->label('Filter Rombel')
                            ->options(function (\Filament\Schemas\Components\Utilities\Get $get) {
                                $academicYearId = $get('academic_year_id');
                                if (!$academicYearId) return \App\Models\StudyGroup::pluck('nama_rombel', 'id');
                                return \App\Models\StudyGroup::where('academic_year_id', $academicYearId)->pluck('nama_rombel', 'id');
                            })
                            ->searchable(),
                    ])
                    ->query(function (\Illuminate\Database\Eloquent\Builder $query, array $data): \Illuminate\Database\Eloquent\Builder {
                        return $query
                            ->when(
                                $data['academic_year_id'] ?? null,
                                fn (\Illuminate\Database\Eloquent\Builder $query, $value): \Illuminate\Database\Eloquent\Builder => $query->whereHas('studyGroup', fn ($q) => $q->where('academic_year_id', $value))
                            )
                            ->when(
                                $data['study_group_id'] ?? null,
                                fn (\Illuminate\Database\Eloquent\Builder $query, $value): \Illuminate\Database\Eloquent\Builder => $query->where('study_group_id', $value)
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
                SelectFilter::make('hari')
                    ->options([
                        'Senin' => 'Senin', 'Selasa' => 'Selasa', 'Rabu' => 'Rabu',
                        'Kamis' => 'Kamis', 'Jumat' => 'Jumat', 'Sabtu' => 'Sabtu',
                    ]),
            ])
            ->actions([
                ViewAction::make()->modal(),
                EditAction::make()->modal(),
                DeleteAction::make()->modal(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('hari');
    }
}
