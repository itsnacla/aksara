<?php

namespace App\Ai\Tools;

use App\Models\EReport;
use App\Models\User;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\Storage;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class GetReportLink implements Tool
{
    public function __construct(protected ?User $user = null) {}

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Mendapatkan link unduh rapor digital (PDF) siswa.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        if (!$this->user) return 'Error: User context missing.';

        $studentId = $request['student_id'] ?? null;
        $roleName = $this->user->roles->first()?->name ?? 'siswa';
        $query = EReport::with('academicYear');

        if (str_contains($roleName, 'siswa')) {
            $query->where('student_id', $this->user->student->id ?? 0);
        } elseif (str_contains($roleName, 'orang_tua')) {
            $childIds = $this->user->parent->students->pluck('id')->toArray();
            if ($studentId && in_array($studentId, $childIds)) {
                $query->where('student_id', $studentId);
            } else {
                $query->whereIn('student_id', $childIds);
            }
        } elseif ($studentId) {
            $query->where('student_id', $studentId);
        }

        $report = $query->latest()->first();
        if (!$report) return 'Rapor tidak ditemukan.';

        return json_encode([
            'semester' => $report->semester,
            'tahun_ajaran' => $report->academicYear->tahun_ajaran ?? 'N/A',
            'download_url' => url(Storage::url($report->file_path)),
            'keterangan' => 'Berikan link ini kepada user agar mereka bisa mengunduh rapornya.',
        ], JSON_PRETTY_PRINT);
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'student_id' => $schema->integer()->description('ID internal siswa (opsional)'),
        ];
    }
}
