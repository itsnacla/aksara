<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\P5Group;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class P5GroupPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:P5Group');
    }

    public function view(AuthUser $authUser, P5Group $p5Group): bool
    {
        return $authUser->can('View:P5Group');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:P5Group');
    }

    public function update(AuthUser $authUser, P5Group $p5Group): bool
    {
        return $authUser->can('Update:P5Group');
    }

    public function delete(AuthUser $authUser, P5Group $p5Group): bool
    {
        return $authUser->can('Delete:P5Group');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:P5Group');
    }

    public function restore(AuthUser $authUser, P5Group $p5Group): bool
    {
        return $authUser->can('Restore:P5Group');
    }

    public function forceDelete(AuthUser $authUser, P5Group $p5Group): bool
    {
        return $authUser->can('ForceDelete:P5Group');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:P5Group');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:P5Group');
    }

    public function replicate(AuthUser $authUser, P5Group $p5Group): bool
    {
        return $authUser->can('Replicate:P5Group');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:P5Group');
    }
}
