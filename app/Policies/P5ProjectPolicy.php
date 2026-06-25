<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\P5Project;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class P5ProjectPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:P5Project');
    }

    public function view(AuthUser $authUser, P5Project $p5Project): bool
    {
        return $authUser->can('View:P5Project');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:P5Project');
    }

    public function update(AuthUser $authUser, P5Project $p5Project): bool
    {
        return $authUser->can('Update:P5Project');
    }

    public function delete(AuthUser $authUser, P5Project $p5Project): bool
    {
        return $authUser->can('Delete:P5Project');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:P5Project');
    }

    public function restore(AuthUser $authUser, P5Project $p5Project): bool
    {
        return $authUser->can('Restore:P5Project');
    }

    public function forceDelete(AuthUser $authUser, P5Project $p5Project): bool
    {
        return $authUser->can('ForceDelete:P5Project');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:P5Project');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:P5Project');
    }

    public function replicate(AuthUser $authUser, P5Project $p5Project): bool
    {
        return $authUser->can('Replicate:P5Project');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:P5Project');
    }
}
