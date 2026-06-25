<?php

namespace App\Filament\Resources\P5Project\RelationManagers;

use App\Models\AcademicYear;
use App\Models\P5Group;
use App\Models\Student;
use App\Models\StudyGroup;
use App\Models\Teacher;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class P5GroupRelationManager extends RelationManager
{
    protected static string $relationship = 'groups';

    protected static ?string $recordTitleAttribute = 'name';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('study_group_id')
                    ->relationship('studyGroup', 'nama_rombel', function ($query, RelationManager $livewire) {
                        $project = $livewire->getOwnerRecord();
                        $activeYearId = AcademicYear::where('is_active', true)->value('id');
                        $levelIds = $project ? $project->levels()->pluck('levels.id')->toArray() : [];

                        // Exclude Rombels already used by this project
                        $usedStudyGroupIds = P5Group::where('p5_project_id', $project?->id)
                            ->whereNotNull('study_group_id')
                            ->pluck('study_group_id')
                            ->toArray();

                        return $query->whereIn('level_id', $levelIds)
                            ->where('academic_year_id', $activeYearId)
                            ->whereNotIn('id', $usedStudyGroupIds);
                    })
                    ->label('Rombel')
                    ->required()
                    ->unique(table: 'p5_groups', column: 'study_group_id', modifyRuleUsing: function ($rule, RelationManager $livewire) {
                        return $rule->where('p5_project_id', $livewire->getOwnerRecord()?->id);
                    }, ignoreRecord: true)
                    ->validationMessages([
                        'unique' => 'Rombel ini sudah ditambahkan ke kegiatan ini.',
                    ])
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set) {
                        if ($state) {
                            $rombel = StudyGroup::find($state);
                            if ($rombel) {
                                $set('name', $rombel->nama_rombel);
                                if ($rombel->walikelas_id) {
                                    $set('teacher_id', $rombel->walikelas_id);
                                }
                                // Load and set all students of this rombel
                                $studentIds = $rombel->students()->pluck('students.id')->toArray();
                                $set('students', $studentIds);
                            }
                        }
                    })
                    ->preload()
                    ->searchable(),

                TextInput::make('name')
                    ->label('Nama Kelompok')
                    ->placeholder('Contoh: 9.1')
                    ->required()
                    ->maxLength(255),

                Select::make('teacher_id')
                    ->options(Teacher::with('user')->get()->pluck('nama_lengkap', 'id'))
                    ->label('Koordinator / Fasilitator')
                    ->required()
                    ->searchable(),

                Select::make('students')
                    ->relationship(
                        name: 'students',
                        titleAttribute: 'id',
                        modifyQueryUsing: function ($query, callable $get) {
                            $rombelId = $get('study_group_id');
                            $query->with('user');
                            if ($rombelId) {
                                $query->whereHas('studyGroups', fn ($q) => $q->where('study_groups.id', $rombelId));
                            }
                        }
                    )
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->user?->name)
                    ->getSearchResultsUsing(function (string $search, callable $get) {
                        $rombelId = $get('study_group_id');
                        $query = Student::with('user');
                        if ($rombelId) {
                            $query->whereHas('studyGroups', fn ($q) => $q->where('study_groups.id', $rombelId));
                        }

                        return $query->whereHas('user', fn ($q) => $q->where('name', 'like', "%{$search}%"))
                            ->limit(50)
                            ->get()
                            ->pluck('user.name', 'id');
                    })
                    ->label('Anggota Kelompok')
                    ->multiple()
                    ->searchable()
                    ->preload(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
                    ->label('Kelompok')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('studyGroup.nama_rombel')
                    ->label('Rombel')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('teacher.nama_lengkap')
                    ->label('Koordinator')
                    ->searchable(false)
                    ->sortable(false),

                TextColumn::make('students_count')
                    ->label('Anggota')
                    ->counts('students')
                    ->badge()
                    ->color('success'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make(),
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

    protected function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['teacher.user', 'studyGroup']);
    }
}
