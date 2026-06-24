<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\StudentParent;
use Illuminate\Auth\Access\HandlesAuthorization;

class StudentParentPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:StudentParent');
    }

    public function view(AuthUser $authUser, StudentParent $studentParent): bool
    {
        return $authUser->can('View:StudentParent');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:StudentParent');
    }

    public function update(AuthUser $authUser, StudentParent $studentParent): bool
    {
        return $authUser->can('Update:StudentParent');
    }

    public function delete(AuthUser $authUser, StudentParent $studentParent): bool
    {
        return $authUser->can('Delete:StudentParent');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:StudentParent');
    }

    public function restore(AuthUser $authUser, StudentParent $studentParent): bool
    {
        return $authUser->can('Restore:StudentParent');
    }

    public function forceDelete(AuthUser $authUser, StudentParent $studentParent): bool
    {
        return $authUser->can('ForceDelete:StudentParent');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:StudentParent');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:StudentParent');
    }

    public function replicate(AuthUser $authUser, StudentParent $studentParent): bool
    {
        return $authUser->can('Replicate:StudentParent');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:StudentParent');
    }

}