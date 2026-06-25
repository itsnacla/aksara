<?php

namespace App\Filament\Pages;

use App\Ai\Agents\DataScientistAssistant;
use App\Models\Attendance;
use App\Models\Grade;
use App\Models\Student;
use App\Models\StudyGroup;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Schemas\Schema;

class PrediksiDropout extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $title = 'Prediksi Risiko Dropout Siswa';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-exclamation-triangle';

    protected string $view = 'filament.pages.prediksi-dropout';

    public static function canAccess(): bool
    {
        return auth()->user()?->can('View:PrediksiDropout') ?? false;
    }

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public ?array $data = [];

    public ?array $aiResult = null;

    public bool $isAnalyzing = false;

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Select::make('study_group_id')
                    ->label('Pilih Rombongan Belajar (Kelas)')
                    ->options(function () {
                        $query = StudyGroup::query()->whereHas('academicYear', function ($q) {
                            $q->where('is_active', true);
                        });

                        $user = auth()->user();
                        $isWaliKelas = $user->hasRole('guru') && $user->teacher
                            && ! $user->teacher->is_kepalasekolah && ! $user->hasRole('super_admin');
                        if ($isWaliKelas) {
                            $query->whereIn('id', $user->teacher->studyGroups()->pluck('id')->toArray());
                        }

                        return $query->pluck('nama_rombel', 'id');
                    })
                    ->searchable()
                    ->live()
                    ->afterStateUpdated(fn (callable $set) => $set('student_id', null))
                    ->required()
                    ->columnSpanFull(),

                Select::make('student_id')
                    ->label('Pilih Siswa')
                    ->options(function (callable $get) {
                        $studyGroupId = $get('study_group_id');
                        if (! $studyGroupId) {
                            return [];
                        }

                        $query = Student::with(['user', 'studyGroups' => function ($q) {
                            $q->whereHas('academicYear', function ($q) {
                                $q->where('is_active', true);
                            });
                        }]);

                        $query->whereHas('studyGroups', function ($q) use ($studyGroupId) {
                            $q->where('study_groups.id', $studyGroupId);
                        });

                        return $query->get()->mapWithKeys(function ($student) {
                            $rombel = $student->studyGroups->first()->nama_rombel ?? '-';
                            $identifier = $student->nis ?? $student->nisn ?? '-';
                            $label = $student->user->name.' - '.$identifier.' - '.$rombel;

                            return [$student->id => $label];
                        });
                    })
                    ->searchable()
                    ->required()
                    ->columnSpanFull(),
            ])
            ->statePath('data');
    }

    public function analyze()
    {
        $this->validate();
        $this->isAnalyzing = true;
        $this->aiResult = null;

        $studentId = $this->data['student_id'];
        $student = Student::with('user')->find($studentId);

        if (! $student) {
            $this->isAnalyzing = false;

            return;
        }

        $grades = Grade::with('subject')->where('student_id', $studentId)->latest()->take(50)->get()->map(fn ($g) => [
            'mapel' => $g->subject->nama_mapel ?? 'N/A',
            'tugas' => $g->nilai_tugas,
            'uts' => $g->nilai_uts,
            'uas' => $g->nilai_uas,
        ]);

        $attendance = Attendance::where('student_id', $studentId)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->get();

        $jsonData = json_encode([
            'student_name' => $student->user->name,
            'grades_history' => $grades,
            'attendance_summary' => $attendance,
        ], JSON_PRETTY_PRINT);

        $prompt = <<<EOT
Tugas Anda: Bertindak sebagai Data Scientist. Analisis data akademik & presensi siswa ini untuk memprediksi risiko dropout atau tinggal kelas.
Anda HARUS merespons HANYA dengan format JSON valid sesuai schema berikut, TANPA markdown block, TANPA teks penjelasan tambahan.

SCHEMA JSON:
{
  "student_name": "Nama Siswa",
  "risk_level": "Rendah / Menengah / Tinggi",
  "risk_score": "Angka probabilitas 0-100",
  "status_color": "Warna representasi (misal: bg-green-500, bg-yellow-500, bg-red-500)",
  "analysis": "Penjelasan paragraf singkat mengapa risiko berada di tingkat tersebut berdasar data.",
  "warning_flags": [
    "Daftar poin-poin spesifik yang menjadi indikator bahaya (contoh: 'Absen tanpa keterangan 5 kali', 'Nilai Matematika di bawah rata-rata', dsb)"
  ],
  "preventive_actions": [
    "Daftar rekomendasi konkrit untuk guru/wali kelas dalam mencegah siswa ini dropout/tinggal kelas"
  ]
}

DATA SISWA:
$jsonData
EOT;

        try {
            $agent = new DataScientistAssistant;
            $response = $agent->prompt($prompt);
            $jsonString = (string) $response;

            // Clean markdown json ticks
            $jsonString = preg_replace('/```json\s*/', '', $jsonString);
            $jsonString = preg_replace('/```/', '', $jsonString);
            $jsonString = trim($jsonString);

            $this->aiResult = json_decode($jsonString, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->aiResult = ['error' => 'AI tidak mengembalikan format JSON yang valid.'];
            }
        } catch (\Exception $e) {
            $this->aiResult = ['error' => 'Gagal terhubung ke AI: '.$e->getMessage()];
        }

        $this->isAnalyzing = false;
    }
}
