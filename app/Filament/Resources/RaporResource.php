<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RaporResource\Pages\ListRapors;
use App\Models\Student;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Illuminate\Database\Eloquent\Builder;

class RaporResource extends Resource
{
    protected static ?string $model = Student::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-document-chart-bar';

    protected static \UnitEnum|string|null $navigationGroup = 'Buku Induk & Rapor';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Rapor Generation';

    protected static ?string $modelLabel = 'Rapor';

    protected static ?string $pluralModelLabel = 'Rapor';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['user', 'studyGroups.level'])
            ->whereHas('studyGroups');
    }

    protected static function getGenerateRaporForm(): array
    {
        return [
            \Filament\Forms\Components\Textarea::make('catatan_wali_kelas')
                ->label('Catatan Wali Kelas (AI Generated & Editable)')
                ->rows(4)
                ->required()
                ->hintAction(
                    Action::make('regenerate_ai')
                        ->label('Regenerate via AI')
                        ->icon('heroicon-m-arrow-path')
                        ->color('primary')
                        ->action(function (\Filament\Schemas\Components\Utilities\Set $set, Student $record) {
                            $activeYearId = \App\Models\AcademicYear::where('is_active', true)->value('id');
                            if (!$activeYearId) return;

                            $raporService = new \App\Services\Academic\RaporService();
                            \App\Models\StudentRapor::where('student_id', $record->id)
                                ->where('academic_year_id', $activeYearId)
                                ->delete();

                            $freshRapor = $raporService->generateStudentRapor($record, $activeYearId);
                            $set('catatan_wali_kelas', $freshRapor->catatan_wali_kelas);

                            \Filament\Notifications\Notification::make()
                                ->title('Catatan berhasil diperbarui menggunakan AI')
                                ->success()
                                ->send();
                        })
                ),
            \Filament\Schemas\Components\Grid::make(2)
                ->schema([
                    \Filament\Forms\Components\Toggle::make('is_naik')
                        ->label(fn (Student $record) => $record->studyGroups->first()?->level?->nama_tingkatan && str_contains($record->studyGroups->first()?->level?->nama_tingkatan, '6') ? 'Keterangan Kelulusan (Lulus / Tinggal)' : 'Keterangan Kenaikan Kelas (Naik / Tidak Naik)')
                        ->default(true),
                    \Filament\Forms\Components\TextInput::make('kenaikan_kelas_to')
                        ->label(fn (Student $record) => $record->studyGroups->first()?->level?->nama_tingkatan && str_contains($record->studyGroups->first()?->level?->nama_tingkatan, '6') ? 'Lulus Ke' : 'Naik Ke Kelas')
                        ->placeholder('e.g. II (Dua) / SMP / Sederajat'),
                ])
                ->visible(fn ($get) => (bool)$get('is_genap')),
        ];
    }

    protected static function getCetakRaporForm(): array
    {
        return [
            \Filament\Forms\Components\Select::make('paper_size')
                ->label('Ukuran Kertas')
                ->options([
                    'a4' => 'A4 (210 x 297 mm)',
                    'f4' => 'F4 / Folio (215 x 330 mm)',
                ])
                ->default('a4')
                ->required(),
            \Filament\Forms\Components\Select::make('margin_size')
                ->label('Margin Halaman')
                ->options([
                    'normal' => 'Normal (10mm)',
                    'sedang' => 'Sedang (7mm)',
                    'sempit' => 'Sempit (5mm)',
                    'none' => 'Tanpa Margin (0mm)',
                ])
                ->default('normal')
                ->required(),
        ];
    }

    public static function table(Table $table): Table
    {
        $table = $table->columns([
            TextColumn::make('nisn')
                ->label('NISN')
                ->searchable()
                ->sortable(),
            TextColumn::make('user.name')
                ->label('Nama Siswa')
                ->searchable()
                ->sortable(),
            TextColumn::make('studyGroups.nama_rombel')
                ->label('Rombongan Belajar')
                ->badge()
                ->color('info'),
            TextColumn::make('studyGroups.level.nama_tingkatan')
                ->label('Tingkat Kelas')
                ->sortable(),
            TextColumn::make('grades_count')
                ->label('Mapel Dinilai')
                ->counts('grades')
                ->badge()
                ->color(fn ($state) => $state > 0 ? 'success' : 'gray')
                ->sortable(),
            IconColumn::make('studentRaporActive')
                ->label('Status Rapor')
                ->boolean()
                ->trueIcon('heroicon-o-check-circle')
                ->falseIcon('heroicon-o-x-circle')
                ->trueColor('success')
                ->falseColor('gray')
                ->getStateUsing(function (Student $record) {
                    $activeYearId = \App\Models\AcademicYear::where('is_active', true)->value('id');
                    if (!$activeYearId) return false;
                    return \App\Models\StudentRapor::where('student_id', $record->id)
                        ->where('academic_year_id', $activeYearId)
                        ->exists();
                }),
        ]);

        $table = $table->filters([
            //
        ]);

        $table = $table->actions([
            Action::make('generate_rapor')
                ->label('Generate Rapor')
                ->icon('heroicon-o-cpu-chip')
                ->color('success')
                ->modalHeading(fn (Student $record) => "Generate Rapor (AI) - {$record->user->name}")
                ->modalWidth('lg')
                ->modalSubmitActionLabel('Simpan Rapor')
                ->fillForm(function (Student $record) {
                    $activeYearId = \App\Models\AcademicYear::where('is_active', true)->value('id');
                    
                    $rapor = \App\Models\StudentRapor::where('student_id', $record->id)
                        ->where('academic_year_id', $activeYearId)
                        ->first();

                    if (!$rapor) {
                        $raporService = new \App\Services\Academic\RaporService();
                        $rapor = $raporService->generateStudentRapor($record, $activeYearId);
                    }

                    $activeYear = \App\Models\AcademicYear::find($activeYearId);
                    $isGenap = $activeYear && strtolower($activeYear->semester) === 'genap';

                    return [
                        'catatan_wali_kelas' => $rapor->catatan_wali_kelas,
                        'is_naik' => $rapor->is_naik ?? true,
                        'kenaikan_kelas_to' => $rapor->kenaikan_kelas_to,
                        'is_genap' => $isGenap,
                    ];
                })
                ->form(self::getGenerateRaporForm())
                ->action(function (Student $record, array $data) {
                    $activeYearId = \App\Models\AcademicYear::where('is_active', true)->value('id');
                    if (!$activeYearId) return;

                    \App\Models\StudentRapor::updateOrCreate(
                        [
                            'student_id' => $record->id,
                            'academic_year_id' => $activeYearId,
                        ],
                        [
                            'catatan_wali_kelas' => $data['catatan_wali_kelas'],
                            'is_naik' => isset($data['is_naik']) ? (bool)$data['is_naik'] : null,
                            'kenaikan_kelas_to' => $data['kenaikan_kelas_to'] ?? null,
                        ]
                    );

                    \Filament\Notifications\Notification::make()
                        ->title('Rapor Berhasil Disimpan')
                        ->success()
                        ->send();
                }),
            Action::make('cetak_rapor')
                ->label('Cetak')
                ->icon('heroicon-o-printer')
                ->color('info')
                ->modalHeading(fn (Student $record) => "Cetak Rapor - {$record->user->name}")
                ->modalWidth('md')
                ->visible(function (Student $record) {
                    $activeYearId = \App\Models\AcademicYear::where('is_active', true)->value('id');
                    if (!$activeYearId) return false;
                    return \App\Models\StudentRapor::where('student_id', $record->id)
                        ->where('academic_year_id', $activeYearId)
                        ->exists();
                })
                ->form(self::getCetakRaporForm())
                ->action(function (Student $record, array $data, \Filament\Resources\Pages\ListRecords $livewire) {
                    $url = route('print.rapor', [
                        'student' => $record->id,
                        'paper_size' => $data['paper_size'],
                        'margin_size' => $data['margin_size'],
                    ]);
                    $livewire->js("window.open('{$url}', '_blank');");
                }),
        ]);

        $table = $table->bulkActions([
            BulkAction::make('generate_rapor_masal')
                ->label('Generate Rapor Masal')
                ->icon('heroicon-o-cpu-chip')
                ->color('success')
                ->modalHeading('Generate Rapor Massal via AI')
                ->modalDescription('Proses ini akan men-generate rapor secara massal menggunakan kecerdasan buatan (AI) untuk menganalisis nilai & absensi seluruh siswa yang dipilih.')
                ->action(function (\Illuminate\Database\Eloquent\Collection $records) {
                    $activeYearId = \App\Models\AcademicYear::where('is_active', true)->value('id');
                    if (!$activeYearId) {
                        \Filament\Notifications\Notification::make()
                            ->title('Tahun ajaran aktif tidak ditemukan')
                            ->danger()
                            ->send();
                        return;
                    }

                    $raporService = new \App\Services\Academic\RaporService();
                    $successCount = 0;

                    foreach ($records as $student) {
                        try {
                            $raporService->generateStudentRapor($student, $activeYearId);
                            $successCount++;
                        } catch (\Exception $e) {
                            \Illuminate\Support\Facades\Log::error("Mass AI Rapor failed for Student ID {$student->id}: " . $e->getMessage());
                        }
                    }

                    \Filament\Notifications\Notification::make()
                        ->title("Rapor berhasil digenerate untuk {$successCount} siswa")
                        ->success()
                        ->send();
                }),
            BulkAction::make('cetak_rapor_masal')
                ->label('Cetak Rapor Masal')
                ->icon('heroicon-o-printer')
                ->color('warning')
                ->modalHeading('Cetak Rapor Massal')
                ->modalWidth('md')
                ->form(self::getCetakRaporForm())
                ->action(function (\Illuminate\Database\Eloquent\Collection $records, array $data, \Filament\Resources\Pages\ListRecords $livewire) {
                    $studentIds = $records->pluck('id')->implode(',');
                    $url = route('print.rapor.bulk', [
                        'student_ids' => $studentIds,
                        'paper_size' => $data['paper_size'],
                        'margin_size' => $data['margin_size'],
                    ]);
                    $livewire->js("window.open('{$url}', '_blank');");
                })
        ]);

        return $table;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRapors::route('/'),
        ];
    }
}
