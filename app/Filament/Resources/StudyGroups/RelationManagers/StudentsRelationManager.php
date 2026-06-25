<?php

namespace App\Filament\Resources\StudyGroups\RelationManagers;

use App\Filament\Resources\Students\Schemas\StudentInfoList;
use App\Models\Student;
use Filament\Actions\AttachAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class StudentsRelationManager extends RelationManager
{
    protected static string $relationship = 'students';

    protected static ?string $title = 'Daftar Siswa';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Grid::make(2)
                ->schema([
                    FileUpload::make('user.photo')
                        ->label('Foto')
                        ->avatar()
                        ->disabled(),
                    Group::make([
                        TextInput::make('user.name')
                            ->label('Nama Lengkap')
                            ->disabled(),
                        TextInput::make('nisn')
                            ->label('NISN')
                            ->disabled(),
                        TextInput::make('nis')
                            ->label('NIS')
                            ->disabled(),
                    ]),
                ]),
        ]);
    }

    public function infolist(Schema $schema): Schema
    {
        return StudentInfoList::configure($schema);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('user.name')
            ->columns([
                Tables\Columns\ImageColumn::make('user.photo')
                    ->label('Foto')
                    ->circular(),
                Tables\Columns\TextColumn::make('nisn')
                    ->label('NISN')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('nis')
                    ->label('NIS')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Nama Siswa')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'aktif' => 'success',
                        'lulus' => 'info',
                        'mutasi' => 'warning',
                        'keluar' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                AttachAction::make()
                    ->label('Tambah Siswa ke Rombel')
                    ->modalHeading('Pilih Siswa')
                    ->recordSelect(
                        fn (AttachAction $action, RelationManager $livewire) => Select::make('recordId')
                            ->label('Pilih Siswa')
                            ->options(function () use ($livewire) {
                                $rombel = $livewire->getOwnerRecord();

                                return Student::query()
                                    ->with('user')
                                    ->where('status', 'aktif')
                                    ->whereDoesntHave('studyGroups', function ($q) use ($rombel) {
                                        $q->where('study_groups.id', $rombel->id);
                                    })
                                    ->get()
                                    ->mapWithKeys(fn ($student) => [
                                        $student->id => "{$student->nisn} - ".($student->user->name ?? 'Unknown'),
                                    ]);
                            })
                            ->searchable()
                    ),
            ])
            ->actions([
                ViewAction::make()->modal()->modalWidth('4xl'),
                DetachAction::make()
                    ->label('Keluarkan dari Rombel'),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DetachBulkAction::make(),
                ]),
            ]);
    }
}
