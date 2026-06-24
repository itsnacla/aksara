<?php

namespace App\Filament\Resources\GraduateProfile;

use App\Filament\Resources\GraduateProfile\Pages;
use App\Models\GraduateProfile;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Filters\SelectFilter;

class GraduateProfileResource extends Resource
{
    protected static ?string $model = GraduateProfile::class;

    protected static ?string $recordTitleAttribute = 'dimensi';

    protected static ?string $slug = 'graduate-profile';

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-academic-cap';

    protected static \UnitEnum|string|null $navigationGroup = 'Pengembangan Diri';

    protected static ?int $navigationSort = 1; // Put it first before Tema, Kegiatan, Kelompok

    protected static ?string $navigationLabel = 'Profil Lulusan';

    protected static ?string $modelLabel = 'Profil Lulusan';

    protected static ?string $pluralModelLabel = 'Profil Lulusan';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Hidden::make('academic_year_id')
                    ->default(fn () => \App\Models\AcademicYear::where('is_active', true)->first()?->id),
                TextInput::make('dimensi')
                    ->label('Dimensi Profil Lulusan')
                    ->placeholder('Contoh: Keimanan dan Ketakwaan terhadap Tuhan YME')
                    ->required()
                    ->maxLength(255),
                Repeater::make('subdimensions')
                    ->relationship()
                    ->label('Subdimensi / Elemen Kunci')
                    ->schema([
                        Textarea::make('subdimensi')
                            ->label('Subdimensi')
                            ->placeholder('Contoh: Keyakinan spiritual, penghayatan ajaran agama...')
                            ->required()
                            ->rows(3),
                    ])
                    ->minItems(1)
                    ->defaultItems(1)
                    ->collapsible()
                    ->itemLabel(fn (array $state): ?string => $state['subdimensi'] ? \Illuminate\Support\Str::limit($state['subdimensi'], 50) : null),
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
                TextColumn::make('subdimensions_count')
                    ->label('Jumlah Subdimensi')
                    ->counts('subdimensions')
                    ->badge()
                    ->color('success'),
                TextColumn::make('subdimensions.subdimensi')
                    ->label('Subdimensi')
                    ->listWithLineBreaks()
                    ->limitList(3)
                    ->expandableLimitedList(),
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

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $query = parent::getEloquentQuery();
        
        // Filter by active academic year
        $activeYearId = \App\Models\AcademicYear::where('is_active', true)->value('id');
        if ($activeYearId) {
            $query->where('academic_year_id', $activeYearId);
        }
        
        return $query;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageGraduateProfiles::route('/'),
        ];
    }
}
