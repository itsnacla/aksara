<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\TeacherSchedule;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class TeacherSchedulePolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:TeacherSchedule');
    }

    public function view(AuthUser $authUser, TeacherSchedule $teacherSchedule): bool
    {
        return $authUser->can('View:TeacherSchedule');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:TeacherSchedule');
    }

    public function update(AuthUser $authUser, TeacherSchedule $teacherSchedule): bool
    {
        return $authUser->can('Update:TeacherSchedule');
    }

    public function delete(AuthUser $authUser, TeacherSchedule $teacherSchedule): bool
    {
        return $authUser->can('Delete:TeacherSchedule');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:TeacherSchedule');
    }

    public function restore(AuthUser $authUser, TeacherSchedule $teacherSchedule): bool
    {
        return $authUser->can('Restore:TeacherSchedule');
    }

    public function forceDelete(AuthUser $authUser, TeacherSchedule $teacherSchedule): bool
    {
        return $authUser->can('ForceDelete:TeacherSchedule');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:TeacherSchedule');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:TeacherSchedule');
    }

    public function replicate(AuthUser $authUser, TeacherSchedule $teacherSchedule): bool
    {
        return $authUser->can('Replicate:TeacherSchedule');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:TeacherSchedule');
    }
}
