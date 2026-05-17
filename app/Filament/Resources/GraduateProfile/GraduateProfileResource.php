<?php

namespace App\Filament\Resources\GraduateProfile;

use App\Filament\Resources\GraduateProfile\Pages;
use App\Models\GraduateProfile;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Filters\SelectFilter;

class GraduateProfileResource extends Resource
{
    protected static ?string $model = GraduateProfile::class;

    protected static ?string $slug = 'graduate-profile';

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-academic-cap';

    protected static \UnitEnum|string|null $navigationGroup = 'Pengembangan Diri & P5';

    protected static ?int $navigationSort = 1; // Put it first before Tema, Kegiatan, Kelompok

    protected static ?string $navigationLabel = 'Profil Lulusan';

    protected static ?string $modelLabel = 'Profil Lulusan';

    protected static ?string $pluralModelLabel = 'Profil Lulusan';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('dimensi')
                    ->label('Dimensi Profil Lulusan')
                    ->placeholder('Contoh: keimanan dan ketakwaan terhadap Tuhan Yang Maha Esa...')
                    ->required()
                    ->maxLength(255),
                TextInput::make('subdimensi')
                    ->label('Subdimensi Profil Lulusan')
                    ->placeholder('Contoh: Hubungan dengan sesama manusia...')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('dimensi')
                    ->label('Dimensi')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('info'),
                TextColumn::make('subdimensi')
                    ->label('Subdimensi')
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('dimensi')
                    ->label('Filter Dimensi')
                    ->options(function() {
                        return GraduateProfile::distinct()->pluck('dimensi', 'dimensi')->toArray();
                    }),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
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
            'index' => Pages\ManageGraduateProfiles::route('/'),
        ];
    }
}
