<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\ChatbotSetting;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class ChatbotSettingPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:ChatbotSetting');
    }

    public function view(AuthUser $authUser, ChatbotSetting $chatbotSetting): bool
    {
        return $authUser->can('View:ChatbotSetting');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:ChatbotSetting');
    }

    public function update(AuthUser $authUser, ChatbotSetting $chatbotSetting): bool
    {
        return $authUser->can('Update:ChatbotSetting');
    }

    public function delete(AuthUser $authUser, ChatbotSetting $chatbotSetting): bool
    {
        return $authUser->can('Delete:ChatbotSetting');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:ChatbotSetting');
    }

    public function restore(AuthUser $authUser, ChatbotSetting $chatbotSetting): bool
    {
        return $authUser->can('Restore:ChatbotSetting');
    }

    public function forceDelete(AuthUser $authUser, ChatbotSetting $chatbotSetting): bool
    {
        return $authUser->can('ForceDelete:ChatbotSetting');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:ChatbotSetting');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:ChatbotSetting');
    }

    public function replicate(AuthUser $authUser, ChatbotSetting $chatbotSetting): bool
    {
        return $authUser->can('Replicate:ChatbotSetting');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:ChatbotSetting');
    }
}
