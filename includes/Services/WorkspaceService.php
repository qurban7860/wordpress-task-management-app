<?php

namespace TMA\TaskManagement\Services;

use TMA\TaskManagement\Database\WorkspaceRepository;
use TMA\TaskManagement\Database\TaskRepository;
use TMA\TaskManagement\Database\ActivityRepository;
use TMA\TaskManagement\Security\Validator;

final class WorkspaceService
{
    public function __construct(
        private WorkspaceRepository $repository,
        private Validator $validator,
        private TaskRepository $taskRepository,
        private ActivityRepository $activityRepository
    ) {}

    public function getForCurrentUser(): array
    {
        $userId = get_current_user_id();
        if ($userId <= 0) {
            return [];
        }

        return $this->repository->ensureDefaultWorkspaces((int) $userId);
    }

    public function ensureDefaultWorkspacesForUser(int $userId): array
    {
        return $this->repository->ensureDefaultWorkspaces($userId);
    }

    public function create(string $name): array
    {
        $userId = get_current_user_id();
        if ($userId <= 0) {
            return ['success' => false, 'message' => 'Authentication required.'];
        }

        $validation = $this->validator->validateWorkspaceName($name);
        if (!$validation['valid']) {
            return ['success' => false, 'message' => $validation['message']];
        }

        if (in_array(strtolower($validation['value']), ['personal', 'work'], true)) {
            return ['success' => false, 'message' => 'Personal and Work are reserved workspace names.'];
        }

        $workspaceId = $this->repository->create((int) $userId, $validation['value']);
        if ($workspaceId <= 0) {
            return ['success' => false, 'message' => 'Unable to create workspace.'];
        }

        return ['success' => true, 'workspace' => $this->repository->getById($workspaceId, (int) $userId)];
    }

    public function update(int $workspaceId, string $name): array
    {
        $userId = get_current_user_id();
        if ($userId <= 0) {
            return ['success' => false, 'message' => 'Authentication required.'];
        }

        $workspace = $this->repository->getById($workspaceId, (int) $userId);
        if (!$workspace) {
            return ['success' => false, 'message' => 'Workspace not found.'];
        }

        if (in_array(strtolower((string) $workspace['name']), ['personal', 'work'], true)) {
            return ['success' => false, 'message' => 'Default workspaces cannot be renamed.'];
        }

        $validation = $this->validator->validateWorkspaceName($name);
        if (!$validation['valid']) {
            return ['success' => false, 'message' => $validation['message']];
        }

        if (in_array(strtolower($validation['value']), ['personal', 'work'], true)) {
            return ['success' => false, 'message' => 'Personal and Work are reserved workspace names.'];
        }

        if (!$this->repository->update($workspaceId, (int) $userId, $validation['value'])) {
            return ['success' => false, 'message' => 'Unable to update workspace.'];
        }

        return ['success' => true, 'workspace' => $this->repository->getById($workspaceId, (int) $userId)];
    }

    public function delete(int $workspaceId): array
    {
        global $wpdb;

        $userId = get_current_user_id();
        if ($userId <= 0) {
            return ['success' => false, 'message' => 'Authentication required.'];
        }

        $workspace = $this->repository->getById($workspaceId, (int) $userId);
        if (!$workspace) {
            return ['success' => false, 'message' => 'Workspace not found.'];
        }

        if (in_array(strtolower((string) $workspace['name']), ['personal', 'work'], true)) {
            return ['success' => false, 'message' => 'Default workspaces cannot be deleted.'];
        }

        $wpdb->query('START TRANSACTION');
        if (
            !$this->taskRepository->deleteForWorkspace($workspaceId, (int) $userId) ||
            !$this->activityRepository->deleteForWorkspace($workspaceId, (int) $userId) ||
            !$this->repository->delete($workspaceId, (int) $userId)
        ) {
            $wpdb->query('ROLLBACK');
            return ['success' => false, 'message' => 'Unable to delete workspace.'];
        }

        $wpdb->query('COMMIT');

        return ['success' => true];
    }
}
