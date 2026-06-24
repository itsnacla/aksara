<?php

namespace App\Filament\Widgets;

use App\Models\Subject;
use App\Models\StudyGroup;
use App\Models\Student;
use App\Services\Academic\GradeProgressBuilder;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Livewire\Attributes\On;
use Illuminate\Database\Eloquent\Builder;

class PerkembanganNilaiTable extends TableWidget
{
    protected static bool $isDiscovered = false;

    public ?int $studyGroupId = null;
    public ?string $studentId = 'all';
    
    // We cache the table data to avoid querying for each cell
    protected array $builderDataCache = [];
    protected bool $dataLoaded = false;

    protected int | string | array $columnSpan = 'full';

    #[On('filter-updated')]
    public function updateFilters($studyGroupId, $studentId)
    {
        $this->studyGroupId = $studyGroupId;
        $this->studentId = $studentId;
        $this->dataLoaded = false; // force reload
    }
    
    protected function loadData()
    {
        if ($this->dataLoaded) return;
        
        if (!$this->studyGroupId) {
            $this->builderDataCache = [];
            $this->dataLoaded = true;
            return;
        }

        $sg = StudyGroup::find($this->studyGroupId);
        if (!$sg) {
            $this->builderDataCache = [];
            $this->dataLoaded = true;
            return;
        }

        $student = null;
        if ($this->studentId && $this->studentId !== 'all') {
            $student = Student::find($this->studentId);
        }

        $builder = new GradeProgressBuilder();
        $this->builderDataCache = $builder->build($sg, $student)['table'];
        $this->dataLoaded = true;
    }

    protected function getSubjectData($subjectName, $semesterColumn)
    {
        $this->loadData();
        
        foreach ($this->builderDataCache as $row) {
            if ($row['nama_mapel'] === $subjectName) {
                if ($semesterColumn === 'rata_rata') {
                    return $row['rata_rata'];
                }
                return $row['semesters'][$semesterColumn] ?? '-';
            }
        }
        
        return '-';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(function (): Builder {
                if (!$this->studyGroupId) {
                    // return empty if no filter
                    return Subject::query()->where('id', 0);
                }
                
                // Only show subjects that have grades in this builder data
                $this->loadData();
                $subjectNames = collect($this->builderDataCache)->pluck('nama_mapel')->toArray();
                
                return Subject::query()->whereIn('nama_mapel', $subjectNames);
            })
            ->columns([
                TextColumn::make('index')
                    ->label('No')
                    ->rowIndex(),
                    
                TextColumn::make('nama_mapel')
                    ->label('Nama Mapel')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('smt_1')
                    ->label('Smt. 1')
                    ->alignCenter()
                    ->state(fn (Subject $record) => $this->getSubjectData($record->nama_mapel, 'Smt. 1')),

                TextColumn::make('smt_2')
                    ->label('Smt. 2')
                    ->alignCenter()
                    ->state(fn (Subject $record) => $this->getSubjectData($record->nama_mapel, 'Smt. 2')),

                TextColumn::make('smt_3')
                    ->label('Smt. 3')
                    ->alignCenter()
                    ->state(fn (Subject $record) => $this->getSubjectData($record->nama_mapel, 'Smt. 3')),

                TextColumn::make('smt_4')
                    ->label('Smt. 4')
                    ->alignCenter()
                    ->state(fn (Subject $record) => $this->getSubjectData($record->nama_mapel, 'Smt. 4')),

                TextColumn::make('smt_5')
                    ->label('Smt. 5')
                    ->alignCenter()
                    ->state(fn (Subject $record) => $this->getSubjectData($record->nama_mapel, 'Smt. 5')),

                TextColumn::make('smt_6')
                    ->label('Smt. 6')
                    ->alignCenter()
                    ->state(fn (Subject $record) => $this->getSubjectData($record->nama_mapel, 'Smt. 6')),

                TextColumn::make('rata_rata')
                    ->label('Rata-Rata')
                    ->alignCenter()
                    ->weight('bold')
                    ->state(fn (Subject $record) => $this->getSubjectData($record->nama_mapel, 'rata_rata')),
            ])
            ->paginated(false)
            ->striped();
    }
}
