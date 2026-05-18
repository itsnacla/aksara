<?php

namespace App\Filament\Resources\StudyGroups\Schemas;

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Level;
use App\Models\Teacher;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;
use Closure;

class StudyGroupForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('academic_year_id')
                    ->label('Tahun Ajaran')
                    ->options(fn () => AcademicYear::where('is_active', true)->get()->mapWithKeys(fn ($year) => [
                        $year->id => "Tahun Ajaran {$year->tahun_ajaran}"
                    ]))
                    ->default(fn () => AcademicYear::where('is_active', true)->first()?->id)
                    ->required()
                    ->searchable()
                    ->live(),
                Select::make('level_id')
                    ->label('Tingkatan')
                    ->relationship('level', 'nama_tingkatan')
                    ->required()
                    ->searchable()
                    ->preload(),
                Select::make('classroom_id')
                    ->label('Ruangan (Fisik)')
                    ->options(function (\Filament\Schemas\Components\Utilities\Get $get, $record) {
                        $academicYearId = $get('academic_year_id');
                        $query = Classroom::query();

                        if ($academicYearId) {
                            $query->whereDoesntHave('studyGroups', function ($q) use ($academicYearId, $record) {
                                $q->where('academic_year_id', $academicYearId);
                                if ($record) {
                                    $q->where('id', '!=', $record->id);
                                }
                            });
                        }

                        return $query->pluck('nama_ruangan', 'id');
                    })
                    ->required()
                    ->searchable()
                    ->preload(),
                Select::make('walikelas_id')
                    ->label('Wali Kelas')
                    ->options(function (\Filament\Schemas\Components\Utilities\Get $get, $record) {
                        $academicYearId = $get('academic_year_id');
                        $query = Teacher::with('user')
                            ->where('status', 'aktif')
                            ->where('is_walikelas', true)
                            ->whereHas('user', fn ($q) => $q->where('is_active', true));

                        if ($academicYearId) {
                            $query->whereDoesntHave('studyGroups', function ($q) use ($academicYearId, $record) {
                                $q->where('academic_year_id', $academicYearId);
                                if ($record) {
                                    $q->where('id', '!=', $record->id);
                                }
                            });
                        }

                        return $query->get()->pluck('nama_lengkap', 'id');
                    })
                    ->required()
                    ->searchable()
                    ->rules([
                        function ($get, $component) {
                            return function (string $attribute, $value, Closure $fail) use ($get, $component) {
                                $academicYearId = $get('academic_year_id');
                                if (!$academicYearId) return;

                                $exists = \App\Models\StudyGroup::where('academic_year_id', $academicYearId)
                                    ->where('walikelas_id', $value)
                                    ->where('id', '!=', $component->getRecord()?->id)
                                    ->exists();
                                
                                if ($exists) {
                                    $fail('Guru ini sudah menjadi Wali Kelas di Rombel lain pada tahun ajaran ini!');
                                }
                            };
                        },
                    ]),
            ]);
    }
}
