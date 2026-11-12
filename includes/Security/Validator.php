<?php

namespace TMA\TaskManagement\Security;

final class Validator
{
    public function validateWorkspaceName(string $name): array
    {
        $trimmed = trim($name);

        if ($trimmed === '') {
            return ['valid' => false, 'message' => 'Workspace name is required.'];
        }

        if (mb_strlen($trimmed) > 255) {
            return ['valid' => false, 'message' => 'Workspace name must be 255 characters or fewer.'];
        }

        return ['valid' => true, 'value' => $trimmed];
    }

    public function validateTask(array $task): array
    {
        $title = trim((string) ($task['title'] ?? ''));
        if ($title === '') {
            return ['valid' => false, 'message' => 'Task title is required.'];
        }

        $priority = strtolower((string) ($task['priority'] ?? 'medium'));
        if (!in_array($priority, ['low', 'medium', 'high'], true)) {
            return ['valid' => false, 'message' => 'Priority must be low, medium, or high.'];
        }

        $status = strtolower((string) ($task['status'] ?? 'todo'));
        if (!in_array($status, ['todo', 'in-progress', 'done'], true)) {
            return ['valid' => false, 'message' => 'Status must be todo, in-progress, or done.'];
        }

        return [
            'valid' => true,
            'values' => [
                'title' => $title,
                'description' => isset($task['description']) ? trim((string) $task['description']) : '',
                'priority' => $priority,
                'status' => $status,
                'due_date' => isset($task['due_date']) && $task['due_date'] !== '' ? sanitize_text_field((string) $task['due_date']) : null,
            ],
        ];
    }
}
