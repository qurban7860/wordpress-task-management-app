<?php

namespace TMA\TaskManagement\Services;

use TMA\TaskManagement\Database\WorkspaceRepository;
use TMA\TaskManagement\Security\Validator;

final class WorkspaceService
{
    public function __construct(
        private WorkspaceRepository $repository,
        private Validator $validator
    ) {}

    public function getForCurrentUser(): array
    {
        $userId = get_current_user_id();
        if ($userId <= 0) {
            return [];
        }

        return $this->repository->allForUser((int) $userId);
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

        $workspaceId = $this->repository->create((int) $userId, $validation['value']);
        if ($workspaceId <= 0) {
            return ['success' => false, 'message' => 'Unable to create workspace.'];
        }

        return ['success' => true, 'workspace' => $this->repository->getById($workspaceId, (int) $userId)];
    }
}
