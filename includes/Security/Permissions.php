<?php

namespace TMA\TaskManagement\Security;

final class Permissions
{
    public function canAccessDashboard(): bool
    {
        return is_user_logged_in() && current_user_can('read');
    }

    public function canManageWorkspace(int $userId): bool
    {
        return get_current_user_id() === $userId || current_user_can('manage_options');
    }

    public function canManageTask(int $userId): bool
    {
        return get_current_user_id() === $userId || current_user_can('manage_options');
    }
}
