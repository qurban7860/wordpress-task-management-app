<?php

namespace TMA\TaskManagement\Services;

use TMA\TaskManagement\Database\ActivityRepository;

final class ActivityService
{
    public function __construct(private ActivityRepository $activityRepository)
    {
    }

    public function getForWorkspace(int $workspaceId): array
    {
        $userId = get_current_user_id();
        if ($userId <= 0) {
            return [];
        }

        return $this->activityRepository->getForWorkspace((int) $userId, $workspaceId, 20);
    }
}
