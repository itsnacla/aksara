<?php

namespace App\Filament\Resources\ExtracurricularGrade\Pages;

use App\Filament\Resources\ExtracurricularGrade\ExtracurricularGradeResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageExtracurricularGrades extends ManageRecords
{
    protected static string $resource = ExtracurricularGradeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('batch_input')
                ->label('Batch Input Nilai')
                ->icon('heroicon-o-table-cells')
                ->color('success')
                ->url(ExtracurricularGradeResource::getUrl('batch-input')),

            CreateAction::make()
                ->label('Tambah Nilai')
                ->modalWidth('2xl')
                ->closeModalByClickingAway(false),
        ];
    }
}
