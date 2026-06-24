<?php

namespace App\Services\Academic;

use App\Models\AcademicYear;
use App\Models\Student;
use App\Models\StudyGroup;
use Illuminate\Support\Facades\DB;

class PromotionService
{
    /**
     * Promote students from one academic year to another.
     */
    public function promote(array $studentIds, int $targetStudyGroupId)
    {
        return DB::transaction(function () use ($studentIds, $targetStudyGroupId) {
            $data = array_map(fn($id) => [
                'student_id' => $id,
                'study_group_id' => $targetStudyGroupId,
                'created_at' => now(),
                'updated_at' => now(),
            ], $studentIds);

            return DB::table('study_group_student')->insert($data);
        });
    }

    /**
     * Automatically duplicate study groups to a new academic year.
     * This helps in "auto-creating" the next year's structure.
     */
    public function duplicateGroupsToYear(int $sourceYearId, int $targetYearId)
    {
        $sourceGroups = StudyGroup::where('academic_year_id', $sourceYearId)->get();
        $targetYear = AcademicYear::findOrFail($targetYearId);

        foreach ($sourceGroups as $group) {
            StudyGroup::create([
                'name' => $group->name, // e.g., "10-A"
                'level_id' => $group->level_id, // We might need to increment this for promotion logic
                'classroom_id' => $group->classroom_id,
                'academic_year_id' => $targetYearId,
                'walikelas_id' => $group->walikelas_id,
            ]);
        }
    }
}
