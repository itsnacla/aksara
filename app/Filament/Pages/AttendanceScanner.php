<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class AttendanceScanner extends Page
{
    protected string $view = 'filament.pages.attendance-scanner';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-qr-code';

    protected static string|\UnitEnum|null $navigationGroup = 'Attendance';

    protected static ?string $navigationLabel = 'QR Scanner';

    protected static ?int $navigationSort = 1;
}