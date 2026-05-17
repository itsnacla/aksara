<?php

namespace App\Filament\Resources\LearningObjective\Pages;

use App\Filament\Resources\LearningObjective\LearningObjectiveResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLearningObjectives extends ListRecords
{
    protected static string $resource = LearningObjectiveResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->modalWidth('lg'),
        ];
    }
}
