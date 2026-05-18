<?php

namespace App\Filament\Resources\TeacherSchedules;

use App\Models\Schedule;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use App\Filament\Resources\Schedules\Tables\SchedulesTable;
use App\Filament\Resources\TeacherSchedules\Pages\ListTeacherSchedules;
use BackedEnum;
use UnitEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class TeacherScheduleResource extends Resource
{
    protected static ?string $model = \App\Models\TeacherSchedule::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-calendar';

    protected static UnitEnum|string|null $navigationGroup = 'Jadwal Pelajaran';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Jadwal Mengajar';

    protected static ?string $pluralModelLabel = 'Jadwal Mengajar';
    
    protected static ?string $modelLabel = 'Jadwal Mengajar';
    
    protected static ?string $slug = 'jadwal-mengajar';

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
            ->with(['startTimeSlot', 'endTimeSlot', 'subject', 'studyGroup.classroom']);
        
        if (auth()->check() && auth()->user()->teacher) {
            $query->where('teacher_id', auth()->user()->teacher->id);
        }
        
        return $query;
    }

    public static function form(\Filament\Schemas\Schema $schema): \Filament\Schemas\Schema
    {
        return \App\Filament\Resources\Schedules\Schemas\ScheduleForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->groups([
                \Filament\Tables\Grouping\Group::make('hari')
                    ->label('Hari')
                    ->getTitleFromRecordUsing(fn ($record) => new \Illuminate\Support\HtmlString("<span style='font-size: 1.1rem; font-weight: bold; text-transform: uppercase; letter-spacing: 0.05em; color: var(--primary-600);'>" . ucfirst($record->hari) . "</span>"))
                    ->collapsible()
                    ->orderQueryUsing(fn (Builder $query, string $direction) => $query->orderByRaw("
                        CASE hari 
                            WHEN 'Senin' THEN 1 
                            WHEN 'Selasa' THEN 2 
                            WHEN 'Rabu' THEN 3 
                            WHEN 'Kamis' THEN 4 
                            WHEN 'Jumat' THEN 5 
                            WHEN 'Sabtu' THEN 6 
                            ELSE 7 
                        END $direction
                    "))
            ])
            ->defaultGroup('hari')
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('startTimeSlot.nama_jam')
                    ->label('Jam Ke-')
                    ->description(fn ($record) => $record->endTimeSlot && $record->endTimeSlot->id !== $record->startTimeSlot->id 
                        ? 's/d ' . $record->endTimeSlot->nama_jam 
                        : null),

                \Filament\Tables\Columns\TextColumn::make('waktu')
                    ->label('Waktu')
                    ->state(function ($record) {
                        $start = $record->startTimeSlot?->waktu_mulai?->format('H:i');
                        $end = $record->endTimeSlot?->waktu_selesai?->format('H:i');
                        return $start && $end ? "{$start} - {$end}" : '-';
                    })
                    ->badge()
                    ->color('success'),

                \Filament\Tables\Columns\TextColumn::make('subject.nama_mapel')
                    ->label('Mata Pelajaran')
                    ->searchable()
                    ->sortable(),

                \Filament\Tables\Columns\TextColumn::make('studyGroup.nama_rombel')
                    ->label('Rombel')
                    ->badge()
                    ->searchable()
                    ->sortable(),

                \Filament\Tables\Columns\TextColumn::make('studyGroup.classroom.nama_ruangan')
                    ->label('Ruangan')
                    ->toggleable(),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('hari')
                    ->options([
                        'Senin' => 'Senin', 'Selasa' => 'Selasa', 'Rabu' => 'Rabu',
                        'Kamis' => 'Kamis', 'Jumat' => 'Jumat', 'Sabtu' => 'Sabtu',
                    ]),
            ])
            ->actions([
                \Filament\Actions\ViewAction::make()->modal(),
            ])
            ->bulkActions([])
            ->defaultSort('start_time_slot_id');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTeacherSchedules::route('/'),
        ];
    }
}
