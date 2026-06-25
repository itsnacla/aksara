<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\StudyGroup;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class StudyGroupPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:StudyGroup');
    }

    public function view(AuthUser $authUser, StudyGroup $studyGroup): bool
    {
        return $authUser->can('View:StudyGroup');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:StudyGroup');
    }

    public function update(AuthUser $authUser, StudyGroup $studyGroup): bool
    {
        return $authUser->can('Update:StudyGroup');
    }

    public function delete(AuthUser $authUser, StudyGroup $studyGroup): bool
    {
        return $authUser->can('Delete:StudyGroup');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:StudyGroup');
    }

    public function restore(AuthUser $authUser, StudyGroup $studyGroup): bool
    {
        return $authUser->can('Restore:StudyGroup');
    }

    public function forceDelete(AuthUser $authUser, StudyGroup $studyGroup): bool
    {
        return $authUser->can('ForceDelete:StudyGroup');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:StudyGroup');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:StudyGroup');
    }

    public function replicate(AuthUser $authUser, StudyGroup $studyGroup): bool
    {
        return $authUser->can('Replicate:StudyGroup');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:StudyGroup');
    }
}
