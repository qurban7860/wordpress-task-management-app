<?php

namespace TMA\TaskManagement\Database;

final class TaskRepository
{
    public function getByWorkspace(int $userId, int $workspaceId, array $filters = []): array
    {
        global $wpdb;

        $table = $wpdb->prefix . 'tma_tasks';
        $where = ['user_id = %d', 'workspace_id = %d'];
        $params = [$userId, $workspaceId];

        if (!empty($filters['status'])) {
            $where[] = 'status = %s';
            $params[] = strtolower((string) $filters['status']);
        }

        if (!empty($filters['priority'])) {
            $where[] = 'priority = %s';
            $params[] = strtolower((string) $filters['priority']);
        }

        if (!empty($filters['search'])) {
            $search = '%' . trim((string) $filters['search']) . '%';
            $where[] = '(title LIKE %s OR description LIKE %s)';
            $params[] = $search;
            $params[] = $search;
        }

        $sortField = in_array((string) ($filters['sort'] ?? 'due_date'), ['title', 'priority', 'status', 'due_date', 'created_at'], true)
            ? (string) $filters['sort']
            : 'due_date';
        $sortDirection = strtoupper((string) ($filters['order'] ?? 'ASC')) === 'DESC' ? 'DESC' : 'ASC';

        $query = $wpdb->prepare(
            sprintf(
                "SELECT * FROM {$table} WHERE %s ORDER BY {$sortField} {$sortDirection}, created_at DESC",
                implode(' AND ', $where)
            ),
            ...$params
        );

        return $wpdb->get_results($query, ARRAY_A) ?: [];
    }

    public function findById(int $id, int $userId): ?array
    {
        global $wpdb;

        $table = $wpdb->prefix . 'tma_tasks';
        $query = $wpdb->prepare(
            "SELECT * FROM {$table} WHERE id = %d AND user_id = %d LIMIT 1",
            $id,
            $userId
        );

        $row = $wpdb->get_row($query, ARRAY_A);

        return $row ?: null;
    }

    public function create(int $userId, int $workspaceId, array $data): int
    {
        global $wpdb;

        $table = $wpdb->prefix . 'tma_tasks';
        $timestamp = gmdate('Y-m-d H:i:s');

        $result = $wpdb->insert(
            $table,
            [
                'user_id' => $userId,
                'workspace_id' => $workspaceId,
                'title' => sanitize_text_field($data['title']),
                'description' => isset($data['description']) ? sanitize_textarea_field($data['description']) : '',
                'priority' => strtolower(sanitize_text_field((string) ($data['priority'] ?? 'medium'))),
                'status' => strtolower(sanitize_text_field((string) ($data['status'] ?? 'todo'))),
                'due_date' => !empty($data['due_date']) ? sanitize_text_field((string) $data['due_date']) : null,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
            ['%d', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s']
        );

        if (false === $result) {
            return 0;
        }

        return (int) $wpdb->insert_id;
    }

    public function update(int $id, int $userId, array $data): bool
    {
        global $wpdb;

        $table = $wpdb->prefix . 'tma_tasks';
        $query = $wpdb->prepare(
            "UPDATE {$table} SET title = %s, description = %s, priority = %s, status = %s, due_date = %s, updated_at = %s WHERE id = %d AND user_id = %d",
            sanitize_text_field($data['title']),
            isset($data['description']) ? sanitize_textarea_field($data['description']) : '',
            strtolower(sanitize_text_field((string) ($data['priority'] ?? 'medium'))),
            strtolower(sanitize_text_field((string) ($data['status'] ?? 'todo'))),
            !empty($data['due_date']) ? sanitize_text_field((string) $data['due_date']) : null,
            gmdate('Y-m-d H:i:s'),
            $id,
            $userId
        );

        return false !== $wpdb->query($query);
    }

    public function delete(int $id, int $userId): bool
    {
        global $wpdb;

        $table = $wpdb->prefix . 'tma_tasks';
        $query = $wpdb->prepare("DELETE FROM {$table} WHERE id = %d AND user_id = %d", $id, $userId);

        return false !== $wpdb->query($query);
    }
}
