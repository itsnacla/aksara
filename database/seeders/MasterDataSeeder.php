<?php
 
namespace Database\Seeders;
 
use App\Models\AcademicYear;
use App\Models\Level;
use App\Models\Subject;
use App\Models\Classroom;
use App\Models\Extracurricular;
use Illuminate\Database\Seeder;
 
class MasterDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->seedAcademicYears();
        $this->seedRooms();
        $levelModels = $this->seedLevels();
        $groupModels = $this->seedSubjectReportGroups();
        $this->seedSubjects($levelModels, $groupModels);
        $this->seedSubjectReportMappings($levelModels);
        $this->seedExtracurriculars();
        $this->seedChatbotSettings();
        // $this->seedLearningObjectives($levelModels); // Commented out - TP data cleaned
        $this->seedP5ThemesAndProjects();
        $this->seedCocurriculars();
    }

    private function seedChatbotSettings(): void
    {
        \App\Models\ChatbotSetting::updateOrCreate(
            ['id' => 1],
            [
                'is_active' => true,
                'primary_provider' => 'google',
                'fallback_providers' => 'groq,openai',
                'settings' => [
                    'google' => [
                        'key' => env('GOOGLE_AI_API_KEY', ''),
                        'model' => 'gemini-2.0-flash',
                    ],
                    'openai' => [
                        'key' => env('OPENAI_API_KEY', ''),
                        'model' => 'gpt-4o-mini',
                        'url' => 'https://api.openai.com/v1',
                    ],
                    'groq' => [
                        'key' => env('GROQ_API_KEY', ''),
                        'model' => 'llama-3.3-70b-versatile',
                    ]
                ]
            ]
        );
    }
 
    private function seedAcademicYears(): void
    {
        $startYear = 2025;
        for ($i = 0; $i < 5; $i++) {
            $yearStr = ($startYear + $i) . '/' . ($startYear + $i + 1);
            
            AcademicYear::firstOrCreate(
                ['tahun_ajaran' => $yearStr],
                [
                    'semester' => 'ganjil',
                    'is_active' => ($i === 0)
                ]
            );
        }
    }
 
    private function seedRooms(): void
    {
        $rooms = ['A', 'B'];
        foreach ($rooms as $roomName) {
            Classroom::firstOrCreate(['nama_ruangan' => $roomName]);
        }
    }
 
    private function seedLevels(): array
    {
        $levels = [
            'Kelas 1' => ['sort' => 1, 'fase' => 'A'],
            'Kelas 2' => ['sort' => 2, 'fase' => 'A'],
            'Kelas 3' => ['sort' => 3, 'fase' => 'B'],
            'Kelas 4' => ['sort' => 4, 'fase' => 'B'],
            'Kelas 5' => ['sort' => 5, 'fase' => 'C'],
            'Kelas 6' => ['sort' => 6, 'fase' => 'C'],
        ];

        $levelModels = [];
        foreach ($levels as $name => $data) {
            $levelModels[$data['sort']] = Level::updateOrCreate(
                ['nama_tingkatan' => $name],
                [
                    'fase' => $data['fase'],
                    'is_last_level' => ($data['sort'] === 6)
                ]
            );
        }
        return $levelModels;
    }

    private function seedSubjectReportGroups(): array
    {
        $groups = [
            ['kelompok' => 'A', 'nama_kelompok' => 'Kelompok A', 'is_active' => true],
            ['kelompok' => 'B', 'nama_kelompok' => 'Kelompok B', 'is_active' => true],
            ['kelompok' => 'C', 'nama_kelompok' => 'Mata Pelajaran Wajib', 'is_active' => true],
            ['kelompok' => 'D', 'nama_kelompok' => 'Mata Pelajaran Pilihan', 'is_active' => true],
            ['kelompok' => 'E', 'nama_kelompok' => 'Kelompok E', 'is_active' => false],
            ['kelompok' => 'F', 'nama_kelompok' => 'Kelompok F', 'is_active' => false],
        ];

        $groupModels = [];
        foreach ($groups as $g) {
            $groupModels[$g['kelompok']] = \App\Models\SubjectReportGroup::updateOrCreate(
                ['kelompok' => $g['kelompok']],
                $g
            );
        }
        return $groupModels;
    }
 
    private function seedSubjects(array $levelModels, array $groupModels): void
    {
        $subjects = [
            ['nama_mapel' => 'Bahasa Indonesia', 'is_umum' => true, 'total_jp' => 6, 'group' => 'A'],
            ['nama_mapel' => 'Bahasa Jawa', 'is_umum' => false, 'total_jp' => 2, 'scheduling_priority' => 2, 'group' => 'B'],
            ['nama_mapel' => 'Bahasa Using', 'is_umum' => false, 'total_jp' => 2, 'scheduling_priority' => 2, 'group' => 'B'],
            ['nama_mapel' => 'Bahasa Inggris', 'is_umum' => false, 'total_jp' => 2, 'scheduling_priority' => 2, 'group' => 'A'],
            ['nama_mapel' => 'Pendidikan Pancasila dan Kewarganegaraan', 'is_umum' => true, 'total_jp' => 6, 'group' => 'A'],
            ['nama_mapel' => 'Pendidikan Agama Islam dan Budi Pekerti', 'is_umum' => false, 'total_jp' => 4, 'scheduling_priority' => 3, 'group' => 'A'],
            ['nama_mapel' => 'Pendidikan Agama Kristen dan Budi Pekerti', 'is_umum' => false, 'total_jp' => 4, 'scheduling_priority' => 3, 'group' => 'A'],
            ['nama_mapel' => 'Pendidikan Agama Katolik dan Budi Pekerti', 'is_umum' => false, 'total_jp' => 4, 'scheduling_priority' => 3, 'group' => 'A'],
            ['nama_mapel' => 'Pendidikan Agama Hindu dan Budi Pekerti', 'is_umum' => false, 'total_jp' => 4, 'scheduling_priority' => 3, 'group' => 'A'],
            ['nama_mapel' => 'Pendidikan Agama Buddha dan Budi Pekerti', 'is_umum' => false, 'total_jp' => 4, 'scheduling_priority' => 3, 'group' => 'A'],
            ['nama_mapel' => 'Pendidikan Agama Konghucu dan Budi Pekerti', 'is_umum' => false, 'total_jp' => 4, 'scheduling_priority' => 3, 'group' => 'A'],
            ['nama_mapel' => 'Pendidikan Jasmani, Olahraga, dan Kesehatan', 'is_umum' => false, 'total_jp' => 4, 'is_one_day_finish' => true, 'scheduling_priority' => 3, 'group' => 'B'],
            ['nama_mapel' => 'Ilmu Pengetahuan Alam dan Sosial (IPAS)', 'is_umum' => true, 'total_jp' => 6, 'group' => 'A'],
            ['nama_mapel' => 'Matematika', 'is_umum' => true, 'total_jp' => 6, 'group' => 'A'],
            ['nama_mapel' => 'Seni Budaya', 'is_umum' => true, 'total_jp' => 4, 'group' => 'B'],
            ['nama_mapel' => 'Upacara', 'is_umum' => true, 'total_jp' => 1, 'scheduling_priority' => 3, 'group' => null, 'is_graded' => false],
        ];
 
        foreach ($subjects as $s) {
            $groupId = isset($s['group'], $groupModels[$s['group']]) ? $groupModels[$s['group']]->id : null;
            $data = [
                'nama_mapel' => $s['nama_mapel'],
                'is_umum' => $s['is_umum'],
                'total_jp' => $s['total_jp'],
                'scheduling_priority' => $s['scheduling_priority'] ?? 1,
                'is_one_day_finish' => $s['is_one_day_finish'] ?? false,
                'subject_report_group_id' => $groupId,
                'is_graded' => $s['is_graded'] ?? true,
            ];

            $subject = Subject::updateOrCreate(['nama_mapel' => $s['nama_mapel']], $data);
            
            $ids = collect($levelModels)
                ->filter(function($model, $sort) use ($s) {
                    if ($s['nama_mapel'] === 'Ilmu Pengetahuan Alam dan Sosial (IPAS)' && in_array($sort, [1, 2])) return false;
                    if ($s['nama_mapel'] === 'Bahasa Using' && in_array($sort, [1, 2, 3])) return false;
                    return true;
                })
                ->pluck('id')->toArray();
 
            $subject->levels()->sync($ids);
        }
    }

    private function seedExtracurriculars(): void
    {
        $ekskuls = [
              ['nama_ekskul' => 'Pramuka', 'kategori' => 'wajib', 'coordinator_name' => 'Imam Fahrudin', 'nilai_minimum' => 'B', 'deskripsi' => 'Kegiatan kepanduan wajib untuk melatih kedisiplinan dan kemandirian.'],
              ['nama_ekskul' => 'Tari', 'kategori' => 'pilihan', 'coordinator_name' => 'Angger Wigunaning Aji', 'nilai_minimum' => 'B', 'deskripsi' => 'Melestarikan seni budaya melalui tarian daerah.'],
              ['nama_ekskul' => 'Hadrah', 'kategori' => 'pilihan', 'coordinator_name' => 'Moh. Itqonur Risal', 'nilai_minimum' => 'B', 'deskripsi' => 'Seni musik islami dengan alat rebana dan hadrah.'],
              ['nama_ekskul' => 'Renang', 'kategori' => 'pilihan', 'coordinator_name' => 'Beni Putra', 'nilai_minimum' => 'B', 'deskripsi' => 'Olahraga renang untuk melatih kesehatan dan ketahanan fisik.'],
        ];

        foreach ($ekskuls as $e) {
            unset($e['coordinator_name']);
            Extracurricular::updateOrCreate(['nama_ekskul' => $e['nama_ekskul']], $e);
        }
    }

    private function seedSubjectReportMappings(array $levelModels): void
    {
        // Mendefinisikan mapel beserta tingkatan (level_id) mana saja yang menggunakan mapel tersebut
        $mappings = [
            [
                'subject_name' => 'Pendidikan Agama Islam dan Budi Pekerti',
                'no_urut' => 1,
                'levels' => [1, 2, 3, 4, 5, 6],
            ],
            [
                'subject_name' => 'Pendidikan Agama Kristen dan Budi Pekerti',
                'no_urut' => 1,
                'levels' => [1, 2, 3, 4, 5, 6],
            ],
            [
                'subject_name' => 'Pendidikan Agama Katolik dan Budi Pekerti',
                'no_urut' => 1,
                'levels' => [1, 2, 3, 4, 5, 6],
            ],
            [
                'subject_name' => 'Pendidikan Agama Hindu dan Budi Pekerti',
                'no_urut' => 1,
                'levels' => [1, 2, 3, 4, 5, 6],
            ],
            [
                'subject_name' => 'Pendidikan Agama Buddha dan Budi Pekerti',
                'no_urut' => 1,
                'levels' => [1, 2, 3, 4, 5, 6],
            ],
            [
                'subject_name' => 'Pendidikan Agama Konghucu dan Budi Pekerti',
                'no_urut' => 1,
                'levels' => [1, 2, 3, 4, 5, 6],
            ],
            [
                'subject_name' => 'Pendidikan Pancasila dan Kewarganegaraan',
                'no_urut' => 2,
                'levels' => [1, 2, 3, 4, 5, 6],
            ],
            [
                'subject_name' => 'Bahasa Indonesia',
                'no_urut' => 3,
                'levels' => [1, 2, 3, 4, 5, 6],
            ],
            [
                'subject_name' => 'Matematika',
                'no_urut' => 4,
                'levels' => [1, 2, 3, 4, 5, 6],
            ],
            [
                'subject_name' => 'Ilmu Pengetahuan Alam dan Sosial (IPAS)',
                'no_urut' => 5,
                'levels' => [3, 4, 5, 6], // IPAS mulai Fase B (Kelas 3)
            ],
            [
                'subject_name' => 'Seni Budaya',
                'no_urut' => 6,
                'levels' => [1, 2, 3, 4, 5, 6],
            ],
            [
                'subject_name' => 'Pendidikan Jasmani, Olahraga, dan Kesehatan',
                'no_urut' => 7,
                'levels' => [1, 2, 3, 4, 5, 6],
            ],
            [
                'subject_name' => 'Bahasa Jawa',
                'no_urut' => 8,
                'levels' => [1, 2, 3, 4, 5, 6],
            ],
            [
                'subject_name' => 'Bahasa Inggris',
                'no_urut' => 9,
                'levels' => [1, 2, 3, 4, 5, 6],
            ],
            [
                'subject_name' => 'Bahasa Using',
                'no_urut' => 10,
                'levels' => [4, 5, 6],
            ],
        ];

        foreach ($mappings as $m) {
            $subject = Subject::where('nama_mapel', $m['subject_name'])->first();
            if ($subject) {
                // Konversi urutan kelas menjadi ID kelas (level)
                $levelIds = [];
                foreach ($m['levels'] as $levelSortId) {
                    if (isset($levelModels[$levelSortId])) {
                        $levelIds[] = $levelModels[$levelSortId]->id;
                    }
                }

                if (!empty($levelIds)) {
                    \App\Models\SubjectReportMapping::updateOrCreate(
                        [
                            'kurikulum' => 'Kurikulum SD Merdeka',
                            'subject_id' => $subject->id,
                        ],
                        [
                            'level_ids' => $levelIds,
                            'no_urut' => $m['no_urut'],
                        ]
                    );
                }
            }
        }
    }

    // private function seedLearningObjectives(array $levelModels): void
    // {
    //     $subjects = Subject::with('levels')->get();
    //     foreach ($subjects as $subject) {
    //         foreach ($levelModels as $sort => $level) {
    //             // Check if subject is associated with this level
    //             if (!$subject->levels->contains($level->id)) {
    //                 continue;
    //             }
    //             
    //             // Let's seed 4 TPs per subject-level combination
    //             $tpTemplates = [
    //                 1 => [
    //                     'code' => "TP {$sort}.1",
    //                     'description' => "memahami konsep dasar {$subject->nama_mapel}"
    //                 ],
    //                 2 => [
    //                     'code' => "TP {$sort}.2",
    //                     'description' => "menganalisis materi {$subject->nama_mapel}"
    //                 ],
    //                 3 => [
    //                     'code' => "TP {$sort}.3",
    //                     'description' => "mengevaluasi hasil karya {$subject->nama_mapel}"
    //                 ],
    //                 4 => [
    //                     'code' => "TP {$sort}.4",
    //                     'description' => "mempraktikkan proyek {$subject->nama_mapel}"
    //                 ],
    //             ];
    //             
    //             foreach ($tpTemplates as $tp) {
    //                 \App\Models\LearningObjective::updateOrCreate(
    //                     [
    //                         'subject_id' => $subject->id,
    //                         'level_id' => $level->id,
    //                         'code' => $tp['code'],
    //                     ],
    //                     [
    //                         'description' => $tp['description'],
    //                         'is_active' => true,
    //                     ]
    //                 );
    //             }
    //         }
    //     }
    // }

    private function seedP5ThemesAndProjects(): void
    {
        $themes = [
            'Gaya Hidup Berkelanjutan' => [
                'Sampahku, Tanggung Jawabku',
                'Pilah dan Olah Sampah Plastik di Sekolah'
            ],
            'Kearifan Lokal' => [
                'Melestarikan Permainan Tradisional Using',
                'Eksplorasi Kuliner Nusantara Banyuwangi'
            ],
            'Bhinneka Tunggal Ika' => [
                'Indahnya Keberagaman di Sekolahku',
                'Pakaian Adat dan Kebudayaan Nusantara'
            ],
            'Kewirausahaan' => [
                'Apotek Hidup dan Budidaya Tanaman Organik',
                'Pasar Cilik SD Merdeka'
            ],
        ];

        foreach ($themes as $themeName => $projects) {
            $theme = \App\Models\P5Theme::updateOrCreate(
                ['name' => $themeName],
                ['is_active' => true]
            );

            foreach ($projects as $projectName) {
                $project = \App\Models\P5Project::updateOrCreate(
                    [
                        'p5_theme_id' => $theme->id,
                        'name' => $projectName,
                    ],
                    [
                        'fase' => 'A', // Default Fase
                        'target_description' => "Projek bertema {$themeName} untuk membina karakter profil pelajar Pancasila melalui kegiatan {$projectName}.",
                        'graduate_profile' => [
                            'Beriman dan Bertaqwa kepada Tuhan YME',
                            'Bergotong Royong',
                            'Bernalar Kritis',
                            'Kreatif'
                        ]
                    ]
                );
                
                // Attach levels for fase A (Level 1 and 2)
                $levelIds = Level::where('fase', 'A')->pluck('id');
                if($levelIds->isNotEmpty()) {
                    $project->levels()->syncWithoutDetaching($levelIds);
                }
            }
        }
    }

    private function seedCocurriculars(): void
    {
        $activeYear = AcademicYear::where('is_active', true)->first();
        $tahunAjaran = $activeYear ? $activeYear->tahun_ajaran : '2025/2026';

        $projects = [
            [
                'tema' => 'Kesehatan Sekolah',
                'nama_projek' => 'Kampanye Cuci Tangan dan Perilaku Hidup Bersih',
                'fase' => 'A',
                'deskripsi' => 'Kampanye kesehatan dan sosialisasi pentingnya mencuci tangan bagi murid-murid Fase A.',
                'tahun_ajaran' => $tahunAjaran,
            ],
            [
                'tema' => 'Literasi Digital',
                'nama_projek' => 'Pengenalan Internet Sehat dan Aman',
                'fase' => 'B',
                'deskripsi' => 'Projek edukasi literasi digital dan keselamatan berinternet untuk murid Fase B.',
                'tahun_ajaran' => $tahunAjaran,
            ]
        ];

        foreach ($projects as $p) {
            \App\Models\Cocurricular::updateOrCreate(
                ['nama_projek' => $p['nama_projek']],
                $p
            );
        }
    }
}
