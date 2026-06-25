<?php

namespace App\Filament\Resources\SchoolSettings;

use App\Filament\Resources\SchoolSettings\Schemas\SchoolSettingForm;
use App\Models\SchoolSetting;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use UnitEnum;

class SchoolSettingResource extends Resource
{
    protected static ?string $model = SchoolSetting::class;

    protected static ?string $recordTitleAttribute = 'name';

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-home-modern';

    protected static UnitEnum|string|null $navigationGroup = 'Sistem & Konfigurasi';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Identitas Sekolah';

    protected static ?string $modelLabel = 'Identitas Sekolah';

    protected static ?string $pluralModelLabel = 'Identitas Sekolah';

    public static function form(Schema $schema): Schema
    {
        return SchoolSettingForm::configure($schema);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageSchoolSetting::route('/'),
        ];
    }
}
