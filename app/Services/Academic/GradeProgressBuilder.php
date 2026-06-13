<?php

namespace App\Services\Academic;

use App\Models\Student;
use App\Models\StudyGroup;
use App\Models\AcademicYear;
use App\Models\Grade;
use App\Models\Subject;

class GradeProgressBuilder
{
    public function build(StudyGroup $studyGroup, ?Student $student = null): array
    {
        $studentIds = $student ? [$student->id] : $studyGroup->students()->pluck('students.id')->toArray();

        if (empty($studentIds)) {
            return $this->emptyStructure();
        }

        // 1. Get all grades for these students
        $grades = Grade::with(['subject', 'academicYear'])
            ->whereIn('student_id', $studentIds)
            ->get();

        // 2. Identify the chronological semesters for these students
        // We will group by academic_year_id to order them.
        $academicYearIds = $grades->pluck('academic_year_id')->unique();
        // Fallback to study group's academic year if no grades
        if ($academicYearIds->isEmpty()) {
            $academicYearIds->push($studyGroup->academic_year_id);
        }

        $academicYears = AcademicYear::whereIn('id', $academicYearIds)
            ->orderBy('tahun_ajaran')
            ->orderBy('semester')
            ->get();

        // Map academic years to Smt 1, 2, 3...
        // We group them by tahun_ajaran to ensure Ganjil is before Genap
        $semesterMap = [];
        $smtIndex = 1;
        $groupedYears = $academicYears->groupBy('tahun_ajaran');
        foreach ($groupedYears as $ta => $years) {
            $ganjil = $years->firstWhere(fn($y) => strtolower($y->semester) === 'ganjil');
            $genap = $years->firstWhere(fn($y) => strtolower($y->semester) === 'genap');
            
            if ($ganjil) {
                $semesterMap[$ganjil->id] = 'Smt. ' . $smtIndex++;
            } else {
                $smtIndex++; // Skip index if missing to keep ganjil=odd, genap=even pattern
            }
            
            if ($genap) {
                $semesterMap[$genap->id] = 'Smt. ' . $smtIndex++;
            } else {
                $smtIndex++;
            }
        }
        
        // Ensure we always have at least Smt 1 to Smt 6 columns
        $maxSmt = max(6, $smtIndex - 1);
        $semesterColumns = [];
        for ($i = 1; $i <= $maxSmt; $i++) {
            $semesterColumns[] = 'Smt. ' . $i;
        }

        // 3. Get Subjects
        $subjectIds = $grades->pluck('subject_id')->unique();
        $subjects = Subject::whereIn('id', $subjectIds)->orderBy('is_umum', 'desc')->orderBy('nama_mapel')->get();

        // 4. Build Table and Chart Data
        $tableData = [];
        $chartCategories = [];
        $chartSeries = [];
        
        // Initialize chart series for each semester
        foreach ($semesterColumns as $col) {
            $chartSeries[$col] = [
                'name' => $col,
                'type' => 'bar',
                'data' => []
            ];
        }
        $chartSeries['Rata-Rata'] = [
            'name' => 'Rata-Rata',
            'type' => 'line',
            'data' => []
        ];

        foreach ($subjects as $subject) {
            $chartCategories[] = $subject->singkatan ?: $subject->nama_mapel;
            
            $row = [
                'nama_mapel' => $subject->nama_mapel,
                'singkatan' => $subject->singkatan ?: $subject->nama_mapel,
                'semesters' => [],
                'rata_rata' => '-'
            ];
            
            $subjectTotal = 0;
            $subjectCount = 0;

            foreach ($semesterColumns as $col) {
                // Find academic year id for this semester column
                $ayId = array_search($col, $semesterMap);
                
                if ($ayId) {
                    $g = $grades->filter(fn($g) => $g->subject_id === $subject->id && $g->academic_year_id === $ayId);
                    if ($g->count() > 0) {
                        $avg = round($g->avg(fn($item) => ($item->nilai_tugas + $item->nilai_uts + $item->nilai_uas) / 3));
                        $row['semesters'][$col] = $avg;
                        $subjectTotal += $avg;
                        $subjectCount++;
                        
                        $chartSeries[$col]['data'][] = $avg;
                    } else {
                        $row['semesters'][$col] = '-';
                        $chartSeries[$col]['data'][] = 0;
                    }
                } else {
                    $row['semesters'][$col] = '-';
                    $chartSeries[$col]['data'][] = 0;
                }
            }
            
            if ($subjectCount > 0) {
                $avgAll = round($subjectTotal / $subjectCount, 2);
                $row['rata_rata'] = $avgAll;
                $chartSeries['Rata-Rata']['data'][] = $avgAll;
            } else {
                $chartSeries['Rata-Rata']['data'][] = 0;
            }
            
            $tableData[] = $row;
        }

        return [
            'columns' => $semesterColumns,
            'table' => $tableData,
            'chart' => [
                'categories' => $chartCategories,
                'series' => array_values($chartSeries)
            ],
            'is_all' => is_null($student)
        ];
    }

    private function emptyStructure(): array
    {
        $cols = ['Smt. 1', 'Smt. 2', 'Smt. 3', 'Smt. 4', 'Smt. 5', 'Smt. 6'];
        $series = array_map(fn($c) => ['name' => $c, 'type' => 'bar', 'data' => []], $cols);
        $series[] = ['name' => 'Rata-Rata', 'type' => 'line', 'data' => []];
        
        return [
            'columns' => $cols,
            'table' => [],
            'chart' => [
                'categories' => [],
                'series' => $series
            ],
            'is_all' => true
        ];
    }
}
