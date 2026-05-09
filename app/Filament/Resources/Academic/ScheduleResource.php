<?php

namespace App\Filament\Resources\Academic;

use App\Models\Schedule;
use App\Models\SchoolClass;
use App\Models\Subject;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;

class ScheduleResource extends Resource
{
    protected static ?string $model = Schedule::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-calendar-days';

    protected static string|\UnitEnum|null $navigationGroup = 'Academic & Curriculum';

    protected static ?string $navigationLabel = 'Schedules';

    public static function form(Schema $form): Schema
    {
        return $form->schema([
            Forms\Components\Select::make('school_class_id')
                ->label('Class')
                ->options(SchoolClass::all()->pluck('nama_kelas', 'id'))
                ->searchable()
                ->required(),

            Forms\Components\Select::make('subject_id')
                ->label('Subject')
                ->options(Subject::all()->pluck('nama_mapel', 'id'))
                ->searchable()
                ->required(),

            Forms\Components\Select::make('hari')
                ->label('Day')
                ->options([
                    'Monday' => 'Monday',
                    'Tuesday' => 'Tuesday',
                    'Wednesday' => 'Wednesday',
                    'Thursday' => 'Thursday',
                    'Friday' => 'Friday',
                    'Saturday' => 'Saturday',
                ])
                ->required(),

            Forms\Components\TimePicker::make('jam_mulai')
                ->label('Start Time')
                ->required(),

            Forms\Components\TimePicker::make('jam_selesai')
                ->label('End Time')
                ->required(),

            Forms\Components\TextInput::make('guru')
                ->label('Teacher'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('schoolClass.nama_kelas')
                    ->label('Class')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('subject.nama_mapel')
                    ->label('Subject')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('hari')
                    ->label('Day')
                    ->sortable(),

                Tables\Columns\TextColumn::make('jam_mulai')
                    ->label('Start Time'),

                Tables\Columns\TextColumn::make('jam_selesai')
                    ->label('End Time'),

                Tables\Columns\TextColumn::make('guru')
                    ->label('Teacher'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('hari')
                    ->label('Day')
                    ->options([
                        'Monday' => 'Monday',
                        'Tuesday' => 'Tuesday',
                        'Wednesday' => 'Wednesday',
                        'Thursday' => 'Thursday',
                        'Friday' => 'Friday',
                        'Saturday' => 'Saturday',
                    ]),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\Academic\ScheduleResource\Pages\ListSchedules::route('/'),
            'create' => \App\Filament\Resources\Academic\ScheduleResource\Pages\CreateSchedule::route('/create'),
            'edit' => \App\Filament\Resources\Academic\ScheduleResource\Pages\EditSchedule::route('/{record}/edit'),
        ];
    }
}