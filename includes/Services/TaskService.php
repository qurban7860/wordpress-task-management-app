<?php

namespace TMA\TaskManagement\Services;

use TMA\TaskManagement\Database\ActivityRepository;
use TMA\TaskManagement\Database\TaskRepository;
use TMA\TaskManagement\Database\WorkspaceRepository;
use TMA\TaskManagement\Security\Permissions;
use TMA\TaskManagement\Security\Validator;

final class TaskService
{
    public function __construct(
        private TaskRepository $taskRepository,
        private ActivityRepository $activityRepository,
        private WorkspaceRepository $workspaceRepository,
        private Validator $validator,
        private Permissions $permissions
    ) {}

    public function getTasksForWorkspace(int $workspaceId, array $filters = []): array
    {
        $userId = get_current_user_id();
        if ($userId <= 0 || !$this->workspaceRepository->getById($workspaceId, (int) $userId)) {
            return [];
        }

        return $this->taskRepository->getByWorkspace((int) $userId, $workspaceId, $filters);
    }

    public function create(int $workspaceId, array $task): array
    {
        $userId = get_current_user_id();
        if ($userId <= 0) {
            return ['success' => false, 'message' => 'Authentication required.'];
        }

        if (!$this->workspaceRepository->getById($workspaceId, $userId)) {
            return ['success' => false, 'message' => 'Workspace not found.'];
        }

        $validation = $this->validator->validateTask($task);
        if (!$validation['valid']) {
            return ['success' => false, 'message' => $validation['message']];
        }

        $taskId = $this->taskRepository->create($userId, $workspaceId, $validation['values']);
        if ($taskId <= 0) {
            return ['success' => false, 'message' => 'Unable to create task.'];
        }

        $this->activityRepository->log($userId, $workspaceId, $taskId, 'created', 'Task created: ' . $validation['values']['title']);

        return ['success' => true, 'task' => $this->taskRepository->findById($taskId, $userId)];
    }

    public function update(int $taskId, array $task): array
    {
        $userId = get_current_user_id();
        if ($userId <= 0) {
            return ['success' => false, 'message' => 'Authentication required.'];
        }

        $existing = $this->taskRepository->findById($taskId, $userId);
        if (!$existing) {
            return ['success' => false, 'message' => 'Task not found.'];
        }

        $validation = $this->validator->validateTask($task);
        if (!$validation['valid']) {
            return ['success' => false, 'message' => $validation['message']];
        }

        $updated = $this->taskRepository->update($taskId, $userId, $validation['values']);
        if (!$updated) {
            return ['success' => false, 'message' => 'Unable to update task.'];
        }

        $this->activityRepository->log($userId, (int) $existing['workspace_id'], $taskId, 'updated', 'Task updated: ' . $validation['values']['title']);

        return ['success' => true, 'task' => $this->taskRepository->findById($taskId, $userId)];
    }

    public function delete(int $taskId): array
    {
        $userId = get_current_user_id();
        if ($userId <= 0) {
            return ['success' => false, 'message' => 'Authentication required.'];
        }

        $existing = $this->taskRepository->findById($taskId, $userId);
        if (!$existing) {
            return ['success' => false, 'message' => 'Task not found.'];
        }

        $deleted = $this->taskRepository->delete($taskId, $userId);
        if (!$deleted) {
            return ['success' => false, 'message' => 'Unable to delete task.'];
        }

        $this->activityRepository->log($userId, (int) $existing['workspace_id'], $taskId, 'deleted', 'Task deleted: ' . $existing['title']);

        return ['success' => true];
    }
}
