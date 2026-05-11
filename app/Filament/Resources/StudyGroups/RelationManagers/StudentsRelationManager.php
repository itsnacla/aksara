<?php

namespace App\Filament\Resources\StudyGroups\RelationManagers;

use Filament\Schemas\Schema;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions\ViewAction;
use Filament\Actions\AssociateAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;

class StudentsRelationManager extends RelationManager
{
    protected static string $relationship = 'students';

    protected static ?string $title = 'Daftar Siswa';

    public function form(Schema $schema): Schema
    {
        // We reuse the StudentForm but maybe simplify it if creating from here
        return $schema->components([
            \Filament\Forms\Components\TextInput::make('user_name')
                ->label('Nama Lengkap')
                ->formatStateUsing(fn ($record) => $record?->user?->name)
                ->disabled(),
            \Filament\Forms\Components\TextInput::make('nisn')
                ->label('NISN')
                ->disabled(),
            \Filament\Forms\Components\TextInput::make('pob')
                ->label('Tempat Lahir')
                ->placeholder('-')
                ->disabled(),
            \Filament\Forms\Components\DatePicker::make('dob')
                ->label('Tanggal Lahir')
                ->placeholder('-')
                ->disabled(),
            \Filament\Forms\Components\TextInput::make('gender')
                ->label('Jenis Kelamin')
                ->formatStateUsing(fn ($state) => $state === 'L' ? 'Laki-laki' : ($state === 'P' ? 'Perempuan' : '-'))
                ->disabled(),
            \Filament\Forms\Components\TextInput::make('religion')
                ->label('Agama')
                ->placeholder('-')
                ->disabled(),
            \Filament\Forms\Components\TextInput::make('phone')
                ->label('No. Telepon')
                ->placeholder('-')
                ->disabled(),
            \Filament\Forms\Components\TextInput::make('parent_name')
                ->label('Orang Tua / Wali')
                ->formatStateUsing(fn ($record) => $record?->parent?->user?->name ?? '-')
                ->disabled(),
            \Filament\Forms\Components\Textarea::make('address')
                ->label('Alamat Lengkap')
                ->placeholder('-')
                ->disabled(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('user.name')
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Nama Siswa')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('nisn')
                    ->label('NISN')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                AssociateAction::make()
                    ->label('Tambah Siswa ke Rombel')
                    ->modalHeading('Pilih Siswa')
                    ->recordSelect(
                        fn (\Filament\Forms\Components\Select $select) => $select
                            ->options(function ($get, $record) {
                                return \App\Models\Student::with('user')
                                    ->where('status', 'aktif')
                                    ->whereDoesntHave('studyGroups', function ($q) use ($record) {
                                        $q->where('study_groups.id', $record->id);
                                    })
                                    ->get()
                                    ->mapWithKeys(fn ($student) => [
                                        $student->id => "{$student->nisn} - " . ($student->user->name ?? 'Unknown')
                                    ]);
                            })
                            ->searchable()
                    ),
            ])
            ->actions([
                ViewAction::make()->modal()->modalWidth('xl'),
                DissociateAction::make()
                    ->label('Keluarkan dari Rombel'),
            ])
            ->bulkActions([
                \Filament\Actions\BulkActionGroup::make([
                    DissociateBulkAction::make(),
                ]),
            ]);
    }
}
