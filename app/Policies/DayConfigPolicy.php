<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\DayConfig;
use Illuminate\Auth\Access\HandlesAuthorization;

class DayConfigPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:DayConfig');
    }

    public function view(AuthUser $authUser, DayConfig $dayConfig): bool
    {
        return $authUser->can('View:DayConfig');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:DayConfig');
    }

    public function update(AuthUser $authUser, DayConfig $dayConfig): bool
    {
        return $authUser->can('Update:DayConfig');
    }

    public function delete(AuthUser $authUser, DayConfig $dayConfig): bool
    {
        return $authUser->can('Delete:DayConfig');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:DayConfig');
    }

    public function restore(AuthUser $authUser, DayConfig $dayConfig): bool
    {
        return $authUser->can('Restore:DayConfig');
    }

    public function forceDelete(AuthUser $authUser, DayConfig $dayConfig): bool
    {
        return $authUser->can('ForceDelete:DayConfig');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:DayConfig');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:DayConfig');
    }

    public function replicate(AuthUser $authUser, DayConfig $dayConfig): bool
    {
        return $authUser->can('Replicate:DayConfig');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:DayConfig');
    }

}