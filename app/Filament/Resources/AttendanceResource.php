<?php

namespace App\Filament\Resources;

use App\Models\Attendance;
use App\Models\Student;
use App\Models\Schedule;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;

class AttendanceResource extends Resource
{
    protected static ?string $model = Attendance::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static string|\UnitEnum|null $navigationGroup = 'Attendance';

    protected static ?string $navigationLabel = 'Attendance List';

    public static function form(Schema $form): Schema
    {
        return $form->schema([
            Forms\Components\Select::make('student_id')
                ->label('Student')
                ->options(
                    Student::with('schoolClass')
                        ->get()
                        ->mapWithKeys(fn($s) => [
                            $s->id => $s->nama_siswa . ' - ' . ($s->schoolClass?->nama_kelas ?? '-')
                        ])
                )
                ->searchable()
                ->required(),

            Forms\Components\Select::make('schedule_id')
                ->label('Schedule')
                ->options(
                    Schedule::with(['schoolClass', 'subject'])
                        ->get()
                        ->mapWithKeys(fn($s) => [
                            $s->id => $s->hari . ' - ' . ($s->subject?->nama_mapel ?? '-') . ' (' . ($s->schoolClass?->nama_kelas ?? '-') . ')'
                        ])
                )
                ->searchable()
                ->nullable(),

            Forms\Components\DatePicker::make('tanggal')
                ->label('Date')
                ->required()
                ->default(now()),

            Forms\Components\Select::make('status')
                ->label('Status')
                ->options([
                    'Present' => 'Present',
                    'Excused' => 'Excused',
                    'Sick' => 'Sick',
                    'Absent' => 'Absent',
                ])
                ->required()
                ->default('Present'),

            Forms\Components\Textarea::make('keterangan')
                ->label('Notes')
                ->rows(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('student.nama_siswa')
                    ->label('Student Name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('student.schoolClass.nama_kelas')
                    ->label('Class')
                    ->sortable(),

                Tables\Columns\TextColumn::make('schedule.subject.nama_mapel')
                    ->label('Subject')
                    ->sortable(),

                Tables\Columns\TextColumn::make('schedule.hari')
                    ->label('Day'),

                Tables\Columns\TextColumn::make('tanggal')
                    ->label('Date')
                    ->date('d M Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'Present' => 'success',
                        'Excused' => 'warning',
                        'Sick' => 'info',
                        'Absent' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('keterangan')
                    ->label('Notes')
                    ->limit(30),
            ])
            ->defaultSort('tanggal', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'Present' => 'Present',
                        'Excused' => 'Excused',
                        'Sick' => 'Sick',
                        'Absent' => 'Absent',
                    ]),

                Tables\Filters\SelectFilter::make('student_id')
                    ->label('Student')
                    ->options(Student::all()->pluck('nama_siswa', 'id')),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\AttendanceResource\Pages\ListAttendances::route('/'),
            'create' => \App\Filament\Resources\AttendanceResource\Pages\CreateAttendance::route('/create'),
            'edit' => \App\Filament\Resources\AttendanceResource\Pages\EditAttendance::route('/{record}/edit'),
        ];
    }
}