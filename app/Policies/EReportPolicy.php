<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\EReport;
use Illuminate\Auth\Access\HandlesAuthorization;

class EReportPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:EReport');
    }

    public function view(AuthUser $authUser, EReport $eReport): bool
    {
        return $authUser->can('View:EReport');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:EReport');
    }

    public function update(AuthUser $authUser, EReport $eReport): bool
    {
        return $authUser->can('Update:EReport');
    }

    public function delete(AuthUser $authUser, EReport $eReport): bool
    {
        return $authUser->can('Delete:EReport');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:EReport');
    }

    public function restore(AuthUser $authUser, EReport $eReport): bool
    {
        return $authUser->can('Restore:EReport');
    }

    public function forceDelete(AuthUser $authUser, EReport $eReport): bool
    {
        return $authUser->can('ForceDelete:EReport');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:EReport');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:EReport');
    }

    public function replicate(AuthUser $authUser, EReport $eReport): bool
    {
        return $authUser->can('Replicate:EReport');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:EReport');
    }

}
