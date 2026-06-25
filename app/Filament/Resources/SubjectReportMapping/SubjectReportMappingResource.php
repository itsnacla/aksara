<?php

namespace App\Filament\Resources\SubjectReportMapping;

use App\Filament\Resources\SubjectReportMapping\Pages\ManageSubjectReportMappings;
use App\Models\Level;
use App\Models\SubjectReportMapping;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

class SubjectReportMappingResource extends Resource
{
    protected static ?string $model = SubjectReportMapping::class;

    protected static ?string $slug = 'subject-report-mapping';

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-arrows-right-left';

    protected static UnitEnum|string|null $navigationGroup = 'Kurikulum & Referensi';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Mapping Mapel Rapor';

    protected static ?string $modelLabel = 'Mapping Mapel Rapor';

    protected static ?string $pluralModelLabel = 'Mapping Mapel Rapor';

    protected static ?string $recordTitleAttribute = 'id';

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
                Select::make('level_ids')
                    ->label('Tingkat Kelas')
                    ->options(Level::pluck('nama_tingkatan', 'id'))
                    ->multiple()
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('subject_id')
                    ->label('Mata Pelajaran')
                    ->relationship('subject', 'nama_mapel', modifyQueryUsing: fn ($query) => $query->where('subjects.is_graded', true))
                    ->searchable()
                    ->preload()
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
                TextColumn::make('level_ids')
                    ->label('Tingkat Kelas')
                    ->badge()
                    ->formatStateUsing(fn ($state) => Level::find($state)?->nama_tingkatan ?? '-'),
                TextColumn::make('subject.nama_mapel')
                    ->label('Nama Mapel')
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
                    ->sortable()
                    ->badge()
                    ->color('success'),
            ])
            ->defaultSort('no_urut', 'asc')
            ->filters([
                SelectFilter::make('kurikulum')
                    ->label('Kurikulum')
                    ->options([
                        'Kurikulum SD Merdeka' => 'Kurikulum SD Merdeka',
                    ]),
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
