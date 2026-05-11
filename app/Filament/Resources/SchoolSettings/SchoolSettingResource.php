<?php

namespace App\Filament\Resources\SchoolSettings;

use App\Filament\Resources\SchoolSettings\Pages;
use App\Models\SchoolSetting;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use App\Filament\Resources\SchoolSettings\Schemas\SchoolSettingForm;
use Filament\Support\Icons\Heroicon;
use UnitEnum;
use BackedEnum;

class SchoolSettingResource extends Resource
{
    protected static ?string $model = SchoolSetting::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-home-modern';

    protected static UnitEnum|string|null $navigationGroup = 'Sistem & Pengaturan';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Identitas Sekolah';
    
    protected static ?string $modelLabel = 'Identitas Sekolah';

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
