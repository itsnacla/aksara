<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\TeacherAttendance;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class TeacherAttendancePolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:TeacherAttendance');
    }

    public function view(AuthUser $authUser, TeacherAttendance $teacherAttendance): bool
    {
        return $authUser->can('View:TeacherAttendance');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:TeacherAttendance');
    }

    public function update(AuthUser $authUser, TeacherAttendance $teacherAttendance): bool
    {
        return $authUser->can('Update:TeacherAttendance');
    }

    public function delete(AuthUser $authUser, TeacherAttendance $teacherAttendance): bool
    {
        return $authUser->can('Delete:TeacherAttendance');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:TeacherAttendance');
    }

    public function restore(AuthUser $authUser, TeacherAttendance $teacherAttendance): bool
    {
        return $authUser->can('Restore:TeacherAttendance');
    }

    public function forceDelete(AuthUser $authUser, TeacherAttendance $teacherAttendance): bool
    {
        return $authUser->can('ForceDelete:TeacherAttendance');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:TeacherAttendance');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:TeacherAttendance');
    }

    public function replicate(AuthUser $authUser, TeacherAttendance $teacherAttendance): bool
    {
        return $authUser->can('Replicate:TeacherAttendance');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:TeacherAttendance');
    }
}
