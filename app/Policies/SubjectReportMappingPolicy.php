<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\SubjectReportMapping;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class SubjectReportMappingPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:SubjectReportMapping');
    }

    public function view(AuthUser $authUser, SubjectReportMapping $subjectReportMapping): bool
    {
        return $authUser->can('View:SubjectReportMapping');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:SubjectReportMapping');
    }

    public function update(AuthUser $authUser, SubjectReportMapping $subjectReportMapping): bool
    {
        return $authUser->can('Update:SubjectReportMapping');
    }

    public function delete(AuthUser $authUser, SubjectReportMapping $subjectReportMapping): bool
    {
        return $authUser->can('Delete:SubjectReportMapping');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:SubjectReportMapping');
    }

    public function restore(AuthUser $authUser, SubjectReportMapping $subjectReportMapping): bool
    {
        return $authUser->can('Restore:SubjectReportMapping');
    }

    public function forceDelete(AuthUser $authUser, SubjectReportMapping $subjectReportMapping): bool
    {
        return $authUser->can('ForceDelete:SubjectReportMapping');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:SubjectReportMapping');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:SubjectReportMapping');
    }

    public function replicate(AuthUser $authUser, SubjectReportMapping $subjectReportMapping): bool
    {
        return $authUser->can('Replicate:SubjectReportMapping');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:SubjectReportMapping');
    }
}
