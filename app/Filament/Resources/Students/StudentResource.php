<?php

namespace App\Filament\Resources\Students;

use App\Models\Student;
use App\Models\SchoolClass;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\Action;

class StudentResource extends Resource
{
    protected static ?string $model = Student::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-user-group';

    protected static string|\UnitEnum|null $navigationGroup = 'Students';

    protected static ?string $navigationLabel = 'Students';

    public static function form(Schema $form): Schema
    {
        return $form->schema([
            Forms\Components\TextInput::make('nama_siswa')
                ->label('Full Name')
                ->required()
                ->maxLength(255),

            Forms\Components\TextInput::make('nisn')
                ->label('Student ID (NISN)')
                ->unique(ignoreRecord: true)
                ->maxLength(20),

            Forms\Components\Select::make('jenis_kelamin')
                ->label('Gender')
                ->options([
                    'Male' => 'Male',
                    'Female' => 'Female',
                ])
                ->required(),

            Forms\Components\Select::make('school_class_id')
                ->label('Class')
                ->options(SchoolClass::all()->pluck('nama_kelas', 'id'))
                ->searchable()
                ->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama_siswa')
                    ->label('Full Name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('nisn')
                    ->label('Student ID')
                    ->searchable(),

                Tables\Columns\TextColumn::make('jenis_kelamin')
                    ->label('Gender'),

                Tables\Columns\TextColumn::make('schoolClass.nama_kelas')
                    ->label('Class')
                    ->sortable(),

                Tables\Columns\TextColumn::make('qr_code')
                    ->label('QR Code')
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('nama_siswa')
            ->actions([
                Action::make('qr_card')
                    ->label('QR Card')
                    ->icon('heroicon-o-qr-code')
                    ->color('info')
                    ->url(fn($record) => route('student.qr-card', $record))
                    ->openUrlInNewTab(),
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\Students\Pages\ListStudents::route('/'),
            'create' => \App\Filament\Resources\Students\Pages\CreateStudent::route('/create'),
            'edit' => \App\Filament\Resources\Students\Pages\EditStudent::route('/{record}/edit'),
        ];
    }
}