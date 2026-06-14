<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\StudyGroup;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Laravel\Ai\Ai;
use Illuminate\Support\HtmlString;

class ClusteringSiswa extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $title = 'Clustering Kemampuan Belajar Siswa';
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-text';
    protected string $view = 'filament.pages.clustering-siswa';

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
                        $query = StudyGroup::query()->whereHas('academicYear', function($q) {
                            $q->where('is_active', true);
                        });
                        
                        $user = auth()->user();
                        $roleName = strtolower($user->roles->first()?->name ?? '');
                        if (str_contains($roleName, 'guru') && $user->teacher) {
                            $query->whereIn('id', $user->teacher->studyGroups()->pluck('id')->toArray());
                        }

                        return $query->pluck('nama_rombel', 'id');
                    })
                    ->searchable()
                    ->required()
                    ->columnSpanFull()
            ])
            ->statePath('data');
    }

    public function analyze()
    {
        $this->validate();
        $this->isAnalyzing = true;
        $this->aiResult = null;

        $studyGroupId = $this->data['study_group_id'];
        $studyGroup = StudyGroup::with(['students.grades.subject', 'students.user'])->find($studyGroupId);

        if (!$studyGroup) {
            $this->isAnalyzing = false;
            return;
        }

        $studentData = [];
        foreach ($studyGroup->students as $student) {
            $subjectGrades = [];
            foreach ($student->grades as $grade) {
                $mapel = $grade->subject->nama_mapel ?? 'Lainnya';
                if (!isset($subjectGrades[$mapel])) {
                    $subjectGrades[$mapel] = [];
                }
                $subjectGrades[$mapel][] = ($grade->nilai_tugas + $grade->nilai_uts + $grade->nilai_uas) / 3;
            }
            
            $averages = [];
            foreach ($subjectGrades as $mapel => $scores) {
                if (count($scores) > 0) {
                    $averages[$mapel] = round(array_sum($scores) / count($scores), 1);
                }
            }
            
            $studentData[] = [
                'nama' => $student->user->name,
                'rata_rata_mapel' => $averages
            ];
        }

        $jsonData = json_encode([
            'class_name' => $studyGroup->nama_rombel,
            'students' => $studentData,
        ], JSON_PRETTY_PRINT);

        $prompt = <<<EOT
Tugas Anda: Lakukan clustering terhadap siswa-siswa ini berdasarkan pola nilai mereka. 
Anda ADALAH mesin Data Science. Anda HARUS merespons HANYA dengan format JSON valid sesuai schema berikut, TANPA markdown block, TANPA teks penjelasan di luar JSON.
PERHATIAN SANGAT PENTING: Anda WAJIB menganalisis dan memasukkan KESELURUHAN SISWA yang ada pada DATA SISWA ke dalam JSON hasil. JANGAN PERNAH meringkas dengan "dll" atau "dsb", JANGAN ADA SATU SISWA PUN YANG TERTINGGAL. Pastikan jumlah array "students" sama dengan data awal.

SCHEMA JSON:
{
  "class_name": "Nama Kelas",
  "total_students": 0,
  "insights": "Satu paragraf singkat kesimpulan analisis keseluruhan dari kelas ini",
  "clusters": [
    {
      "name": "Nama Kategori Gaya Belajar (misal: Logika Kuat, Seni/Bahasa, Seimbang, Kurang di Eksakta, dll)",
      "color": "Warna hex atau tailwind class (misal: bg-blue-100, bg-green-100, bg-yellow-100, bg-red-100)",
      "percentage": "Persentase jumlah siswa di cluster ini dibanding total",
      "students": ["Nama Siswa 1", "Nama Siswa 2"],
      "recommendation": "Rekomendasi pedagogis ringkas untuk guru bagi kelompok ini"
    }
  ]
}

DATA SISWA:
$jsonData
EOT;

        try {
            $agent = new \App\Ai\Agents\DataScientistAssistant();
            $response = $agent->prompt($prompt);
            $jsonString = (string) $response;
            
            // Clean markdown json ticks if AI included them
            $jsonString = preg_replace('/```json\s*/', '', $jsonString);
            $jsonString = preg_replace('/```/', '', $jsonString);
            $jsonString = trim($jsonString);

            $this->aiResult = json_decode($jsonString, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                \Illuminate\Support\Facades\Log::error('AI JSON Parse Error', ['raw_response' => $jsonString]);
                $this->aiResult = ['error' => 'AI tidak mengembalikan format JSON yang valid. Silakan coba lagi.'];
            }
        } catch (\Exception $e) {
            $this->aiResult = ['error' => 'Gagal terhubung ke AI: ' . $e->getMessage()];
        }

        $this->isAnalyzing = false;
    }
}
