<?php

namespace TMA\TaskManagement\Services;

use TMA\TaskManagement\Database\ActivityRepository;
use TMA\TaskManagement\Database\WorkspaceRepository;

final class ActivityService
{
    public function __construct(
        private ActivityRepository $activityRepository,
        private WorkspaceRepository $workspaceRepository
    )
    {
    }

    public function getForWorkspace(int $workspaceId): array
    {
        $userId = get_current_user_id();
        if ($userId <= 0 || !$this->workspaceRepository->getById($workspaceId, (int) $userId)) {
            return [];
        }

        return $this->activityRepository->getForWorkspace((int) $userId, $workspaceId, 20);
    }
}
