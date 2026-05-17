<?php

namespace App\Filament\Resources\BukuInduk;

use App\Filament\Resources\BukuInduk\Pages\ListBukuIndukKelas1s;
use App\Models\Student;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Illuminate\Database\Eloquent\Builder;

class BukuIndukKelas1Resource extends Resource
{
    protected static ?string $model = Student::class;

    protected static ?string $slug = 'buku-induk';

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-book-open';

    protected static \UnitEnum|string|null $navigationGroup = 'Buku Induk & Rapor';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Buku Induk';

    protected static ?string $modelLabel = 'Buku Induk';

    protected static ?string $pluralModelLabel = 'Buku Induk';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['user', 'studyGroups.level']);
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
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('academic_year')
                    ->label('Tahun Ajaran')
                    ->options(fn () => \App\Models\AcademicYear::query()
                        ->get()
                        ->mapWithKeys(fn ($year) => [$year->id => "{$year->tahun_ajaran} - " . ucfirst($year->semester)])
                    )
                    ->query(function ($query, array $data) {
                        if (empty($data['value'])) {
                            return $query;
                        }
                        return $query->whereHas('studyGroups', function ($q) use ($data) {
                            $q->where('study_groups.academic_year_id', $data['value']);
                        });
                    })
                    ->default(fn () => \App\Models\AcademicYear::where('is_active', true)->first()?->id),
                \Filament\Tables\Filters\SelectFilter::make('studyGroups')
                    ->label('Filter Rombel')
                    ->relationship('studyGroups', 'nama_rombel', function ($query, $livewire) {
                        $academicYearId = $livewire->tableFilters['academic_year']['value'] ?? null;
                        $academicYearId = $academicYearId ?: \App\Models\AcademicYear::where('is_active', true)->first()?->id;
                        if ($academicYearId) {
                            return $query->where('academic_year_id', $academicYearId);
                        }
                        return $query;
                    })
                    ->default(fn () => \App\Models\StudyGroup::whereHas('academicYear', fn ($q) => $q->where('is_active', true))
                        ->whereHas('level', fn ($q) => $q->where('nama_tingkatan', 'like', '%Kelas 1%'))
                        ->first()?->id),
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
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBukuIndukKelas1s::route('/'),
        ];
    }
}
