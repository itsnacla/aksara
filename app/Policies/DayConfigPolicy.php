<?php

namespace App\Policies;

use App\Models\User;
use App\Models\DayConfig;
use Illuminate\Auth\Access\HandlesAuthorization;

class DayConfigPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\User  $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_day::config');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\DayConfig  $dayConfig
     * @return bool
     */
    public function view(User $user, DayConfig $dayConfig): bool
    {
        return $user->can('view_day::config');
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return bool
     */
    public function create(User $user): bool
    {
        return $user->can('create_day::config');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\DayConfig  $dayConfig
     * @return bool
     */
    public function update(User $user, DayConfig $dayConfig): bool
    {
        return $user->can('update_day::config');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\DayConfig  $dayConfig
     * @return bool
     */
    public function delete(User $user, DayConfig $dayConfig): bool
    {
        return $user->can('delete_day::config');
    }

    /**
     * Determine whether the user can bulk delete the model.
     *
     * @param  \App\Models\User  $user
     * @return bool
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_day::config');
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\DayConfig  $dayConfig
     * @return bool
     */
    public function forceDelete(User $user, DayConfig $dayConfig): bool
    {
        return $user->can('force_delete_day::config');
    }

    /**
     * Determine whether the user can permanently bulk delete the model.
     *
     * @param  \App\Models\User  $user
     * @return bool
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any_day::config');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\DayConfig  $dayConfig
     * @return bool
     */
    public function restore(User $user, DayConfig $dayConfig): bool
    {
        return $user->can('restore_day::config');
    }

    /**
     * Determine whether the user can bulk restore the model.
     *
     * @param  \App\Models\User  $user
     * @return bool
     */
    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any_day::config');
    }

    /**
     * Determine whether the user can replicate the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\DayConfig  $dayConfig
     * @return bool
     */
    public function replicate(User $user, DayConfig $dayConfig): bool
    {
        return $user->can('replicate_day::config');
    }

    /**
     * Determine whether the user can reorder the models.
     *
     * @param  \App\Models\User  $user
     * @return bool
     */
    public function reorder(User $user): bool
    {
        return $user->can('reorder_day::config');
    }
}
