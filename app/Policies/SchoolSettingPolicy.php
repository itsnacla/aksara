<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\SchoolSetting;
use Illuminate\Auth\Access\HandlesAuthorization;

class SchoolSettingPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:SchoolSetting');
    }

    public function view(AuthUser $authUser, SchoolSetting $schoolSetting): bool
    {
        return $authUser->can('View:SchoolSetting');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:SchoolSetting');
    }

    public function update(AuthUser $authUser, SchoolSetting $schoolSetting): bool
    {
        return $authUser->can('Update:SchoolSetting');
    }

    public function delete(AuthUser $authUser, SchoolSetting $schoolSetting): bool
    {
        return $authUser->can('Delete:SchoolSetting');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:SchoolSetting');
    }

    public function restore(AuthUser $authUser, SchoolSetting $schoolSetting): bool
    {
        return $authUser->can('Restore:SchoolSetting');
    }

    public function forceDelete(AuthUser $authUser, SchoolSetting $schoolSetting): bool
    {
        return $authUser->can('ForceDelete:SchoolSetting');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:SchoolSetting');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:SchoolSetting');
    }

    public function replicate(AuthUser $authUser, SchoolSetting $schoolSetting): bool
    {
        return $authUser->can('Replicate:SchoolSetting');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:SchoolSetting');
    }

}