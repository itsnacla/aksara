<?php

namespace App\Services\Academic;

use App\Models\Student;
use App\Models\StudentRapor;

class BukuIndukService
{
    protected RaporService $raporService;

    public function __construct()
    {
        $this->raporService = new RaporService();
    }

    /**
     * Generate Buku Induk data for a student.
     *
     * @param Student $student
     * @param int $academicYearId
     * @return StudentRapor
     */
    public function generateStudentBukuInduk(Student $student, int $academicYearId): StudentRapor
    {
        // Currently, generating Buku Induk is integrated with the generation of StudentRapor
        // which prepares all attendance recaps and class descriptions required for records.
        return $this->raporService->generateStudentRapor($student, $academicYearId);
    }
}
