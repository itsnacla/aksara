<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SubjectReportGroupResource\Pages\ManageSubjectReportGroups;
use App\Models\SubjectReportGroup;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use UnitEnum;

class SubjectReportGroupResource extends Resource
{
    protected static ?string $model = SubjectReportGroup::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-folder-open';

    protected static UnitEnum|string|null $navigationGroup = 'Kurikulum & Referensi';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Kelompok Mapel Rapor';

    protected static ?string $modelLabel = 'Kelompok Mapel Rapor';

    protected static ?string $pluralModelLabel = 'Kelompok Mapel Rapor';

    protected static ?string $recordTitleAttribute = 'nama_kelompok';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('kelompok')
                    ->label('Kelompok (Kode)')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(10)
                    ->placeholder('Contoh: A, B, C...'),
                TextInput::make('nama_kelompok')
                    ->label('Nama Kelompok')
                    ->required()
                    ->maxLength(100)
                    ->placeholder('Contoh: Kelompok A, Mata Pelajaran Wajib...'),
                Toggle::make('is_active')
                    ->label('Status Aktif')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('kelompok')
                    ->label('Kelompok')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('nama_kelompok')
                    ->label('Nama Kelompok')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('is_active')
                    ->label('Status')
                    ->badge()
                    ->color(fn (bool $state): string => $state ? 'success' : 'danger')
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Aktif' : 'Tidak Aktif')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                EditAction::make()->modal(),
                DeleteAction::make()->modal(),
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
            'index' => ManageSubjectReportGroups::route('/'),
        ];
    }
}
