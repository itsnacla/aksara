<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SubjectReportMappingResource\Pages\ManageSubjectReportMappings;
use App\Models\SubjectReportMapping;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use UnitEnum;

class SubjectReportMappingResource extends Resource
{
    protected static ?string $model = SubjectReportMapping::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-arrows-right-left';

    protected static UnitEnum|string|null $navigationGroup = 'Kurikulum & Referensi';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Mapping Mapel Rapor';

    protected static ?string $modelLabel = 'Mapping Mapel Rapor';

    protected static ?string $pluralModelLabel = 'Mapping Mapel Rapor';

    protected static ?string $recordTitleAttribute = 'nama_lokal';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('kurikulum')
                    ->label('Kurikulum Sekolah')
                    ->options([
                        'Kurikulum SD Merdeka' => 'Kurikulum SD Merdeka',
                    ])
                    ->default('Kurikulum SD Merdeka')
                    ->required(),
                Select::make('level_id')
                    ->label('Tingkat Kelas')
                    ->relationship('level', 'nama_tingkatan')
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('subject_id')
                    ->label('Mata Pelajaran (Global)')
                    ->relationship('subject', 'nama_mapel', modifyQueryUsing: fn($query) => $query->where('subjects.is_graded', true))
                    ->searchable()
                    ->preload()
                    ->required(),
                TextInput::make('nama_lokal')
                    ->label('Nama Lokal (Di Rapor)')
                    ->placeholder('Contoh: Pendidikan Agama Islam dan Budi Pekerti, Bahasa Sunda...')
                    ->maxLength(100)
                    ->required(),
                TextInput::make('no_urut')
                    ->label('No Urut Rapor')
                    ->placeholder('Contoh: 1, 2, 3...')
                    ->numeric()
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('kurikulum')
                    ->label('Kurikulum')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('level.nama_tingkatan')
                    ->label('Tingkat')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('subject.nama_mapel')
                    ->label('Nama Mapel (Global)')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('nama_lokal')
                    ->label('Nama Lokal')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('subject.subjectReportGroup.nama_kelompok')
                    ->label('Kelompok Rapor')
                    ->badge()
                    ->color('info')
                    ->placeholder('-')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('no_urut')
                    ->label('No Urut Rapor')
                    ->badge()
                    ->color('gray')
                    ->alignCenter()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('kurikulum')
                    ->label('Kurikulum')
                    ->options([
                        'Kurikulum SD Merdeka' => 'Kurikulum SD Merdeka',
                    ]),
                SelectFilter::make('level_id')
                    ->label('Tingkat Kelas')
                    ->relationship('level', 'nama_tingkatan'),
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
            'index' => ManageSubjectReportMappings::route('/'),
        ];
    }
}
