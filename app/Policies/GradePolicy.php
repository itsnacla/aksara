<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Grade;
use Illuminate\Auth\Access\HandlesAuthorization;

class GradePolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('view_any_grade') || $authUser->hasRole('guru');
    }

    public function view(AuthUser $authUser, Grade $grade): bool
    {
        if ($authUser->can('view_grade')) return true;
        
        if ($authUser->hasRole('guru') && $authUser->teacher) {
            $teacherId = $authUser->teacher->id;
            
            // Allow if Subject Teacher OR Homeroom Teacher
            return $grade->teacher_id === $teacherId || 
                   $grade->studyGroup?->walikelas_id === $teacherId;
        }

        return false;
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('create_grade') || $authUser->hasRole('guru');
    }

    public function update(AuthUser $authUser, Grade $grade): bool
    {
        if ($authUser->can('update_grade')) return true;

        if ($authUser->hasRole('guru') && $authUser->teacher) {
            $teacherId = $authUser->teacher->id;
            
            // Subject Teacher can update their own subjects
            if ($grade->teacher_id === $teacherId) return true;
            
            // Homeroom Teacher can update anything in their rombel? 
            // Usually only Subject Teacher, but maybe user wants it.
            return $grade->studyGroup?->walikelas_id === $teacherId;
        }

        return false;
    }

    public function delete(AuthUser $authUser, Grade $grade): bool
    {
        return $authUser->can('delete_grade');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('delete_any_grade');
    }

    public function restore(AuthUser $authUser, Grade $grade): bool
    {
        return $authUser->can('Restore:Grade');
    }

    public function forceDelete(AuthUser $authUser, Grade $grade): bool
    {
        return $authUser->can('ForceDelete:Grade');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Grade');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Grade');
    }

    public function replicate(AuthUser $authUser, Grade $grade): bool
    {
        return $authUser->can('Replicate:Grade');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Grade');
    }

}
