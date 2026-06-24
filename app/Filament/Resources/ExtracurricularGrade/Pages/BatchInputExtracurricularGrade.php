<?php

namespace App\Filament\Resources\ExtracurricularGrade\Pages;

use App\Filament\Resources\ExtracurricularGrade\ExtracurricularGradeResource;
use App\Models\AcademicYear;
use App\Models\Extracurricular;
use App\Models\ExtracurricularGrade;
use App\Models\Student;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Livewire\Attributes\Url;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\WithPagination;

class BatchInputExtracurricularGrade extends Page implements HasForms
{
    use InteractsWithForms, WithPagination;

    protected static string $resource = ExtracurricularGradeResource::class;

    protected static ?string $title = 'Batch Input Nilai Ekskul';

    protected string $view = 'filament.resources.extracurricular-grade.pages.batch-input-extracurricular-grade';

    public ?array $data = [];

    #[Url]
    public ?int $extracurricular_id = null;

    #[Url]
    public ?int $rombel_id = null;

    #[Url]
    public ?bool $missing_only = false;

    public array $allGrades = [];

    public int $perPage = 20;

    public function mount(): void
    {
        $this->batchForm->fill([
            'extracurricular_id' => $this->extracurricular_id,
        ]);
        
        if ($this->extracurricular_id) {
            $this->loadAllStudents();
            $this->fillCurrentPage();
        }
    }

    // Livewire hook — save current page data BEFORE the page changes
    public function updatingPage(): void
    {
        $this->saveCurrentPageToAllGrades();
    }

    // Livewire hook — fill form AFTER page changes
    public function updatedPage(): void
    {
        $this->fillCurrentPage();
    }

    // Livewire hook — fires after extracurricular is selected
    public function updatedDataExtracurricularId($value): void
    {
        $this->extracurricular_id = $value ? (int) $value : null;
        $this->resetPage();
        $this->loadAllStudents();
        $this->fillCurrentPage();
    }

    protected function getForms(): array
    {
        return ['batchForm'];
    }

    public function batchForm(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->schema([
                Section::make()
                    ->schema([
                        Select::make('extracurricular_id')
                            ->label('Ekstrakurikuler')
                            ->options(function () {
                                $query = Extracurricular::orderBy('nama_ekskul');
                                $user = auth()->user();
                                if ($user && !$user->hasAnyRole(['super_admin', 'staff'])) {
                                    $query->where('coordinator_user_id', $user->id);
                                }
                                return $query->pluck('nama_ekskul', 'id');
                            })
                            ->searchable()
                            ->required()
                            ->live()
                            ->columnSpanFull(),
                    ]),

                Section::make('Daftar Penilaian Siswa')
                    ->description(fn () => "Total {$this->getTotalStudents()} siswa — Halaman {$this->getPage()} dari {$this->getTotalPages()}")
                    ->schema([
                        Repeater::make('pageItems')
                            ->label('')
                            ->schema([
                                Hidden::make('student_id'),
                                TextInput::make('student_name')
                                    ->label('Nama Siswa')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->columnSpan(3),
                                TextInput::make('rombel')
                                    ->label('Rombel')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->columnSpan(1),
                                Select::make('predikat')
                                    ->label('Predikat')
                                    ->options(ExtracurricularGrade::$predikatOptions)
                                    ->default('B')
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function (
                                        ?string $state,
                                        \Filament\Schemas\Components\Utilities\Set $set
                                    ) {
                                        if ($state) {
                                            $set('keterangan', ExtracurricularGrade::$defaultKeterangan[$state] ?? '');
                                        }
                                    })
                                    ->columnSpan(2),
                                TextInput::make('keterangan')
                                    ->label('Keterangan')
                                    ->placeholder('Keterangan singkat (opsional)')
                                    ->columnSpan(6),
                            ])
                            ->columns(12)
                            ->addable(false)
                            ->deletable(false)
                            ->reorderable(false),
                    ])
                    ->visible(fn () => !empty($this->allGrades)),
            ]);
    }

    private function getStudentsQuery(): \Illuminate\Support\Collection
    {
        $query = Student::whereHas(
            'extracurriculars',
            fn ($q) => $q->where('extracurriculars.id', $this->extracurricular_id)
        );

        if ($this->rombel_id) {
            $query->whereHas('studyGroups', fn ($q) => $q->where('study_groups.id', $this->rombel_id));
        }

        return $query->with([
                'user',
                'studyGroups' => fn ($q) => $q->whereHas('academicYear', fn ($ay) => $ay->where('is_active', true)),
            ])
            ->get()
            ->sortBy('user.name')
            ->values();
    }

    private function getExistingGrades(int $academicYearId): \Illuminate\Support\Collection
    {
        return ExtracurricularGrade::where('extracurricular_id', $this->extracurricular_id)
            ->where('academic_year_id', $academicYearId)
            ->get()
            ->keyBy('student_id');
    }

    private function loadAllStudents(): void
    {
        if (!$this->extracurricular_id) {
            $this->allGrades = [];
            return;
        }

        $academicYearId = AcademicYear::where('is_active', true)->value('id');

        $students = $this->getStudentsQuery();
        $existing = $this->getExistingGrades($academicYearId);

        if ($this->missing_only) {
            $students = $students->filter(fn ($student) => !isset($existing[$student->id]))->values();
        }

        $this->allGrades = $students->map(function ($student) use ($existing) {
            $predikat = $existing[$student->id]->predikat ?? 'B';
            $keterangan = $existing[$student->id]->keterangan ?? null;
            
            if (empty($keterangan)) {
                $keterangan = ExtracurricularGrade::$defaultKeterangan[$predikat] ?? '';
            }

            return [
                'student_id'   => $student->id,
                'student_name' => $student->user?->name ?? 'Unknown',
                'rombel'       => $student->studyGroups->first()?->nama_rombel ?? '-',
                'predikat'     => $predikat,
                'keterangan'   => $keterangan,
            ];
        })->toArray();
    }

    private function saveCurrentPageToAllGrades(): void
    {
        $pageItems = array_values($this->data['pageItems'] ?? []);
        $offset = ($this->getPage() - 1) * $this->perPage;

        foreach ($pageItems as $i => $item) {
            $globalIndex = $offset + $i;
            if (isset($this->allGrades[$globalIndex])) {
                $this->allGrades[$globalIndex]['predikat'] = $item['predikat'] ?? 'B';
                $this->allGrades[$globalIndex]['keterangan'] = $item['keterangan'] ?? '';
            }
        }
    }

    private function fillCurrentPage(): void
    {
        $offset = ($this->getPage() - 1) * $this->perPage;
        $pageItems = array_values(array_slice($this->allGrades, $offset, $this->perPage));

        $this->batchForm->fill([
            'extracurricular_id' => $this->extracurricular_id,
            'pageItems'          => $pageItems,
        ]);
    }

    public function getPaginator(): LengthAwarePaginator
    {
        $offset = ($this->getPage() - 1) * $this->perPage;
        $items  = array_values(array_slice($this->allGrades, $offset, $this->perPage));

        return new LengthAwarePaginator(
            $items,
            count($this->allGrades),
            $this->perPage,
            $this->getPage(),
            ['path' => request()->url()]
        );
    }

    public function getTotalPages(): int
    {
        if (empty($this->allGrades)) {
            return 0;
        }
        return (int) ceil(count($this->allGrades) / $this->perPage);
    }

    public function getTotalStudents(): int
    {
        return count($this->allGrades);
    }

    public function save(): void
    {
        $this->saveCurrentPageToAllGrades();

        $academicYearId = AcademicYear::where('is_active', true)->value('id');

        if (!$academicYearId || !$this->extracurricular_id) {
            Notification::make()->title('Gagal')->body('Tidak ada tahun ajaran aktif.')->danger()->send();
            return;
        }

        $user = auth()->user();
        if ($user && !$user->hasAnyRole(['super_admin', 'staff'])) {
            $isCoordinator = Extracurricular::where('id', $this->extracurricular_id)
                ->where('coordinator_user_id', $user->id)
                ->exists();
            if (!$isCoordinator) {
                Notification::make()
                    ->title('Tidak diizinkan')
                    ->body('Hanya koordinator ekstrakurikuler yang bisa menginput nilai untuk ekskul ini.')
                    ->danger()
                    ->send();
                return;
            }
        }

        $saved = 0;
        foreach ($this->allGrades as $item) {
            if (empty($item['predikat'])) {
                continue;
            }

            ExtracurricularGrade::updateOrCreate(
                [
                    'extracurricular_id' => $this->extracurricular_id,
                    'student_id'         => $item['student_id'],
                    'academic_year_id'   => $academicYearId,
                ],
                [
                    'predikat'   => $item['predikat'],
                    'keterangan' => $item['keterangan'] ?: null,
                ]
            );
            $saved++;
        }

        Notification::make()
            ->title("Berhasil menyimpan {$saved} nilai ekskul")
            ->success()
            ->send();
    }
}
