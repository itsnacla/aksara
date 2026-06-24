<?php

namespace App\Services\Academic;

use App\Models\Student;
use App\Models\AcademicYear;
use App\Models\Grade;
use App\Models\Attendance;
use App\Models\ExtracurricularGrade;
use App\Models\Subject;
use App\Models\Extracurricular;

class BukuIndukDataBuilder
{
    public function getBukuIndukData(Student $student): array
    {
        // Get all academic years the student has grades, attendances, or study groups for.
        // For simplicity, just get all academic years and we'll filter by those where student has study group.
        $student->load(['studyGroups.academicYear']);
        
        $years = collect();
        foreach ($student->studyGroups as $sg) {
            if ($sg->academicYear) {
                $years->push($sg->academicYear);
            }
        }
        
        // Also get years from grades just in case
        $gradeYears = AcademicYear::whereIn('id', Grade::where('student_id', $student->id)->select('academic_year_id'))->get();
        $years = $years->merge($gradeYears)->unique('id');

        // Group by tahun_ajaran
        $groupedYears = $years->groupBy('tahun_ajaran')->sortKeys();
        
        $chunks = [];
        $currentChunk = [];
        foreach ($groupedYears as $tahunAjaran => $academicYears) {
            $ganjil = $academicYears->firstWhere(fn($y) => strtolower($y->semester) === 'ganjil');
            $genap = $academicYears->firstWhere(fn($y) => strtolower($y->semester) === 'genap');
            
            $currentChunk[$tahunAjaran] = [
                'ganjil' => $ganjil ? $ganjil->id : null,
                'genap' => $genap ? $genap->id : null,
            ];
            
            if (count($currentChunk) === 3) {
                $chunks[] = $currentChunk;
                $currentChunk = [];
            }
        }
        if (count($currentChunk) > 0) {
            $chunks[] = $currentChunk;
        }
        
        // If no chunks, provide an empty chunk of current year
        if (empty($chunks)) {
            $activeYear = AcademicYear::where('is_active', true)->first();
            if ($activeYear) {
                $chunks[] = [
                    $activeYear->tahun_ajaran => [
                        'ganjil' => strtolower($activeYear->semester) === 'ganjil' ? $activeYear->id : null,
                        'genap' => strtolower($activeYear->semester) === 'genap' ? $activeYear->id : null,
                    ]
                ];
            } else {
                $chunks[] = [];
            }
        }

        // Fetch all grades for this student
        $grades = Grade::with('subject')->where('student_id', $student->id)->get();
        
        // Fetch all attendances
        $attendances = Attendance::with('studyGroup.academicYear')->where('student_id', $student->id)->get();
        
        // Fetch all ekskuls
        $ekskuls = ExtracurricularGrade::with(['extracurricular', 'academicYear'])->where('student_id', $student->id)->get();
        
        // All subjects that have ever been graded for this student
        $subjectIds = $grades->pluck('subject_id')->unique();
        $subjects = Subject::whereIn('id', $subjectIds)->orderBy('is_umum', 'desc')->orderBy('nama_mapel')->get();

        $chunkedData = [];
        
        foreach ($chunks as $chunkYears) {
            $chunkData = [
                'tahun_ajarans' => array_keys($chunkYears),
                'semesters' => $chunkYears,
                'subjects' => [],
                'ekskuls' => [],
                'attendances' => [
                    'sakit' => [],
                    'izin' => [],
                    'alpha' => [],
                ]
            ];
            
            // Build subjects
            foreach ($subjects as $subject) {
                $subjectRow = ['nama' => $subject->nama_mapel, 'grades' => []];
                foreach ($chunkYears as $ta => $sems) {
                    // Ganjil
                    $gGanjil = $grades->first(fn($g) => $g->subject_id === $subject->id && $g->academic_year_id === $sems['ganjil']);
                    $subjectRow['grades'][$ta]['ganjil'] = $gGanjil ? round(($gGanjil->nilai_tugas + $gGanjil->nilai_uts + $gGanjil->nilai_uas)/3) : '-';
                    // Genap
                    $gGenap = $grades->first(fn($g) => $g->subject_id === $subject->id && $g->academic_year_id === $sems['genap']);
                    $subjectRow['grades'][$ta]['genap'] = $gGenap ? round(($gGenap->nilai_tugas + $gGenap->nilai_uts + $gGenap->nilai_uas)/3) : '-';
                }
                $chunkData['subjects'][] = $subjectRow;
            }
            
            // Build ekskuls
            $ekskulIds = $ekskuls->pluck('extracurricular_id')->unique();
            $ekskulModels = Extracurricular::whereIn('id', $ekskulIds)->get();
            foreach ($ekskulModels as $eks) {
                $eksRow = ['nama' => $eks->nama_ekskul, 'grades' => []];
                foreach ($chunkYears as $ta => $sems) {
                    $eGanjil = $ekskuls->first(fn($e) => $e->extracurricular_id === $eks->id && $e->academic_year_id === $sems['ganjil']);
                    $eksRow['grades'][$ta]['ganjil'] = $eGanjil ? $eGanjil->predikat : '-';
                    
                    $eGenap = $ekskuls->first(fn($e) => $e->extracurricular_id === $eks->id && $e->academic_year_id === $sems['genap']);
                    $eksRow['grades'][$ta]['genap'] = $eGenap ? $eGenap->predikat : '-';
                }
                $chunkData['ekskuls'][] = $eksRow;
            }
            
            // Build attendances
            foreach ($chunkYears as $ta => $sems) {
                $attGanjil = $attendances->filter(fn($a) => $a->studyGroup && $a->studyGroup->academic_year_id === $sems['ganjil']);
                $attGenap = $attendances->filter(fn($a) => $a->studyGroup && $a->studyGroup->academic_year_id === $sems['genap']);
                
                $chunkData['attendances']['sakit'][$ta]['ganjil'] = $attGanjil->where('status', 'sakit')->count() ?: '-';
                $chunkData['attendances']['sakit'][$ta]['genap'] = $attGenap->where('status', 'sakit')->count() ?: '-';
                
                $chunkData['attendances']['izin'][$ta]['ganjil'] = $attGanjil->where('status', 'izin')->count() ?: '-';
                $chunkData['attendances']['izin'][$ta]['genap'] = $attGenap->where('status', 'izin')->count() ?: '-';
                
                $chunkData['attendances']['alpha'][$ta]['ganjil'] = $attGanjil->where('status', 'alpha')->count() ?: '-';
                $chunkData['attendances']['alpha'][$ta]['genap'] = $attGenap->where('status', 'alpha')->count() ?: '-';
            }
            
            $chunkedData[] = $chunkData;
        }
        
        return $chunkedData;
    }
}
