<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BukuIndukKelas1Resource\Pages\ListBukuIndukKelas1s;
use App\Models\Student;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\Action;
use Illuminate\Database\Eloquent\Builder;

class BukuIndukKelas1Resource extends Resource
{
    protected static ?string $model = Student::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-book-open';

    protected static \UnitEnum|string|null $navigationGroup = 'Buku Induk & Rapor';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Buku Induk Kelas 1';

    protected static ?string $modelLabel = 'Buku Induk Kelas 1';

    protected static ?string $pluralModelLabel = 'Buku Induk Kelas 1';

    public static function getEloquentQuery(): Builder
    {
        // Only load students currently active in Level 1 (Kelas 1)
        return parent::getEloquentQuery()
            ->with(['user', 'studyGroups.level'])
            ->whereHas('studyGroups', function ($q) {
                $q->whereHas('level', function ($ql) {
                    $ql->where('nama_tingkatan', 'like', '%Kelas 1%');
                });
            });
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nisn')
                    ->label('NISN')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('nis')
                    ->label('NIS')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('user.name')
                    ->label('Nama Siswa')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('gender')
                    ->label('JK')
                    ->formatStateUsing(fn ($state) => $state === 'L' ? 'Laki-laki' : ($state === 'P' ? 'Perempuan' : '-'))
                    ->sortable(),
                TextColumn::make('studyGroups.nama_rombel')
                    ->label('Rombongan Belajar')
                    ->badge()
                    ->color('success'),
                TextColumn::make('ayah_nama')
                    ->label('Nama Ayah')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('ibu_nama')
                    ->label('Nama Ibu')
                    ->searchable()
                    ->toggleable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Action::make('cetak')
                    ->label('Cetak')
                    ->icon('heroicon-o-printer')
                    ->color('success')
                    ->url(fn (Student $record): string => route('print.buku-induk', $record))
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([
                //
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBukuIndukKelas1s::route('/'),
        ];
    }
}
