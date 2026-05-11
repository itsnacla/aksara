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
            SelectFilter::make('status')
                ->options([
                    'aktif' => 'Aktif',
                    'lulus' => 'Lulus',
                    'mutasi' => 'Mutasi',
                    'keluar' => 'Keluar',
                ]),
            SelectFilter::make('studyGroups')
                ->relationship('studyGroups', 'nama_rombel')
                ->label('Filter Rombel'),
        ];
    }

    protected static function getActions(): array
    {
        return [
            ViewAction::make()
                ->modal()
                ->mutateRecordDataUsing(function (array $data, $record): array {
                    $user = $record->user;
                    if ($user) {
                        $data['user_name'] = $user->name;
                        $data['user_username'] = $user->username;
                        $data['user_email'] = $user->email;
                        $data['user_photo'] = $user->photo;
                        $data['user_is_active'] = $user->is_active;
                    }
                    return $data;
                }),   
            EditAction::make()
                ->modal()
                ->mutateRecordDataUsing(function (array $data, $record): array {
                    $user = $record->user;
                    if ($user) {
                        $data['user_name'] = $user->name;
                        $data['user_username'] = $user->username;
                        $data['user_email'] = $user->email;
                        $data['user_photo'] = $user->photo;
                        $data['user_is_active'] = $user->is_active;
                    }
                    return $data;
                })
                ->mutateFormDataUsing(function (array $data, $record): array {
                    $user = $record->user;
                    if ($user) {
                        $updateData = array_filter([
                            'name' => $data['user_name'] ?? null,
                            'username' => $data['user_username'] ?? null,
                            'email' => $data['user_email'] ?? null,
                            'is_active' => $data['user_is_active'] ?? true,
                        ]);

                        if (!empty($data['user_password'])) {
                            $updateData['password'] = Hash::make($data['user_password']);
                        }

                        if (isset($data['user_photo'])) {
                            $updateData['photo'] = $data['user_photo'];
                        }

                        $user->update($updateData);
                    }

                    unset($data['user_name'], $data['user_username'], $data['user_email'], $data['user_password'], $data['user_photo'], $data['user_is_active']);

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
                            ->options(fn (Get $get) => StudyGroup::where('academic_year_id', $get('academic_year_id'))->pluck('nama_rombel', 'id'))
                            ->required(),
                    ])
                    ->action(function (Collection $records, array $data): void {
                        foreach ($records as $record) {
                            $record->studyGroups()->syncWithoutDetaching([$data['study_group_id']]);
                        }
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