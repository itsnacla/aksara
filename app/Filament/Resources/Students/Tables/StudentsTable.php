<?php

namespace App\Filament\Resources\Students\Tables;

use App\Models\StudyGroup;
use App\Models\AcademicYear;
use Illuminate\Support\Facades\Hash;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Filament\Forms\Components\Select;
use Filament\Actions\Action;
use Filament\Schemas\Components\Utilities\Get;
use Illuminate\Database\Eloquent\Collection;

class StudentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns(static::getColumns())
            ->filters(static::getFilters())
            ->actions(static::getActions())
            ->bulkActions(static::getBulkActions());
    }

    protected static function getColumns(): array
    {
        return [
            \Filament\Tables\Columns\ImageColumn::make('user.photo')
                ->label('Foto')
                ->circular(),

            TextColumn::make('nisn')
                ->label('NISN')
                ->searchable()
                ->sortable(),

            TextColumn::make('nis')
                ->label('NIS')
                ->searchable()
                ->sortable(),

            TextColumn::make('user.name')
                ->label('Nama Siswa')
                ->searchable()
                ->sortable(),

            TextColumn::make('studyGroups.nama_rombel')
                ->label('Rombel')
                ->badge()
                ->sortable(),

            TextColumn::make('status')
                ->label('Status')
                ->badge()
                ->color(fn (string $state): string => match ($state) {
                    'aktif' => 'success',
                    'lulus' => 'info',
                    'mutasi' => 'warning',
                    'keluar' => 'danger',
                    default => 'gray',
                })
                ->sortable(),

            \Filament\Tables\Columns\IconColumn::make('user.is_active')
                ->label('Akun Aktif')
                ->boolean(),
        ];
    }

    protected static function getFilters(): array
    {
        return [
            SelectFilter::make('academic_year')
                ->label('Tahun Ajaran')
                ->options(fn () => AcademicYear::query()
                    ->get()
                    ->mapWithKeys(fn ($year) => [$year->id => "{$year->tahun_ajaran} - " . ucfirst($year->semester)])
                )
                ->query(function ($query, array $data) {
                    if (empty($data['value'])) {
                        return $query;
                    }
                    return $query->whereHas('studyGroups', function ($q) use ($data) {
                        $q->where('academic_year_id', $data['value']);
                    });
                })
                ->default(fn () => AcademicYear::where('is_active', true)->first()?->id),
            SelectFilter::make('status')
                ->options([
                    'aktif' => 'Aktif',
                    'lulus' => 'Lulus',
                    'mutasi' => 'Mutasi',
                    'keluar' => 'Keluar',
                ]),
            SelectFilter::make('studyGroups')
                ->label('Filter Rombel')
                ->relationship('studyGroups', 'nama_rombel', function ($query, $get) {
                    $academicYearId = $get('academic_year');
                    if ($academicYearId) {
                        return $query->where('academic_year_id', $academicYearId);
                    }
                    return $query;
                }),
        ];
    }

    protected static function getActions(): array
    {
        return [
            ViewAction::make()
                ->modal()
                ->modalWidth('7xl')
                ->mutateRecordDataUsing(function (array $data, $record): array {
                    $user = $record->user;
                    if ($user) {
                        $data['user_name'] = $user->name;
                        $data['user_username'] = $user->username;
                        $data['user_email'] = $user->email;
                        $data['user_photo'] = $user->photo;
                        $data['user_is_active'] = $user->is_active;
                    }
                    $parent = $record->parent;
                    if ($parent) {
                        $data['parent'] = $parent->toArray();
                    }
                    return $data;
                }),   
            EditAction::make()
                ->modal()
                ->modalWidth('7xl')
                ->mutateRecordDataUsing(function (array $data, $record): array {
                    $user = $record->user;
                    if ($user) {
                        $data['user_name'] = $user->name;
                        $data['user_username'] = $user->username;
                        $data['user_email'] = $user->email;
                        $data['user_photo'] = $user->photo;
                        $data['user_is_active'] = $user->is_active;
                    }
                    $parent = $record->parent;
                    if ($parent) {
                        $data['parent'] = $parent->toArray();
                    }
                    return $data;
                })
                ->mutateFormDataUsing(function (array $data, $record): array {
                    // 1. Handle User Update
                    $user = $record->user;
                    if ($user) {
                        $updateUserData = [
                            'name' => $data['user_name'] ?? $user->name,
                            'username' => $data['user_username'] ?? $user->username,
                            'email' => $data['user_email'] ?? $user->email,
                            'is_active' => $data['user_is_active'] ?? true,
                        ];

                        if (!empty($data['user_password'])) {
                            $updateUserData['password'] = Hash::make($data['user_password']);
                        }

                        if (isset($data['user_photo'])) {
                            $updateUserData['photo'] = $data['user_photo'];
                        }

                        $user->update($updateUserData);
                    }

                    // 2. Handle Parent Update
                    $parent = $record->parent;
                    if ($parent && isset($data['parent'])) {
                        $parent->update($data['parent']);
                    }

                    unset($data['user_name'], $data['user_username'], $data['user_email'], $data['user_password'], $data['user_photo'], $data['user_is_active'], $data['parent']);

                    return $data;
                }),
            Action::make('print_card')
                ->label('Cetak Kartu')
                ->icon('heroicon-o-identification')
                ->color('info')
                ->url(fn ($record) => route('student.card', $record))
                ->openUrlInNewTab(),
            \Filament\Actions\DeleteAction::make()
                ->modal(),
        ];
    }

    protected static function getBulkActions(): array
    {
        return [
            BulkActionGroup::make([
                DeleteBulkAction::make(),
                BulkAction::make('pindah_rombel')
                    ->label('Pindah Rombel')
                    ->icon('heroicon-o-arrows-right-left')
                    ->form([
                        Select::make('academic_year_id')
                            ->label('Tahun Ajaran')
                            ->options(fn () => AcademicYear::all()->mapWithKeys(fn ($year) => [$year->id => "{$year->tahun_ajaran} - " . ucfirst($year->semester)]))
                            ->required()
                            ->live(),
                        Select::make('study_group_id')
                            ->label('Pilih Rombel Baru')
                            ->options(fn (Get $get) => StudyGroup::where('academic_year_id', $get('academic_year_id'))->pluck('nama_rombel', 'id'))
                            ->required(),
                    ])
                    ->action(function (Collection $records, array $data): void {
                        foreach ($records as $record) {
                            $record->studyGroups()->syncWithoutDetaching([$data['study_group_id']]);
                        }
                    }),
                BulkAction::make('naik_kelas')
                    ->label('Naik Kelas')
                    ->icon('heroicon-o-chevron-double-up')
                    ->form([
                        Select::make('academic_year_id')
                            ->label('Tahun Ajaran Baru')
                            ->options(fn () => AcademicYear::all()->mapWithKeys(fn ($year) => [$year->id => "{$year->tahun_ajaran} - " . ucfirst($year->semester)]))
                            ->required()
                            ->live(),
                        Select::make('study_group_id')
                            ->label('Pilih Rombel Baru')
                            ->helperText('Kosongkan pilihan Rombel jika siswa akan diluluskan (berlaku untuk siswa tingkat akhir).')
                            ->options(fn (Get $get) => StudyGroup::where('academic_year_id', $get('academic_year_id'))->pluck('nama_rombel', 'id')),
                    ])
                    ->action(function (Collection $records, array $data): void {
                        $graduatedCount = 0;
                        $promotedCount = 0;

                        foreach ($records as $record) {
                            if (empty($data['study_group_id'])) {
                                // Check if student is in the last level
                                $currentRombel = $record->studyGroups()->whereHas('academicYear', fn($q) => $q->where('is_active', true))->first();
                                if ($currentRombel && $currentRombel->level && $currentRombel->level->is_last_level) {
                                    $record->update(['status' => 'lulus']);
                                    $graduatedCount++;
                                }
                            } else {
                                $record->studyGroups()->syncWithoutDetaching([$data['study_group_id']]);
                                $record->update(['status' => 'aktif']);
                                $promotedCount++;
                            }
                        }

                        $message = "Proses selesai. ";
                        if ($promotedCount > 0) $message .= "{$promotedCount} siswa naik kelas. ";
                        if ($graduatedCount > 0) $message .= "{$graduatedCount} siswa diluluskan.";

                        \Filament\Notifications\Notification::make()
                            ->title($message)
                            ->success()
                            ->send();
                    }),
                BulkAction::make('print_cards_bulk')
                    ->label('Cetak Kartu Massal')
                    ->icon('heroicon-o-identification')
                    ->color('info')
                        ->action(function (Collection $records) {
                            $ids = $records->pluck('id')->implode(',');
                            return redirect()->route('student.cards.bulk', ['ids' => $ids]);
                        }),
                ]),
        ];
    }
}