<?php

namespace TMA\TaskManagement\Database;

final class ActivityRepository
{
    public function log(int $userId, int $workspaceId, ?int $taskId, string $actionType, string $description): int
    {
        global $wpdb;

        $table = $wpdb->prefix . 'tma_activity_logs';
        $timestamp = gmdate('Y-m-d H:i:s');

        $result = $wpdb->insert(
            $table,
            [
                'user_id' => $userId,
                'workspace_id' => $workspaceId,
                'task_id' => $taskId,
                'action_type' => sanitize_text_field($actionType),
                'description' => sanitize_textarea_field($description),
                'created_at' => $timestamp,
            ],
            ['%d', '%d', '%d', '%s', '%s', '%s']
        );

        if (false === $result) {
            return 0;
        }

        return (int) $wpdb->insert_id;
    }

    public function getForWorkspace(int $userId, int $workspaceId, int $limit = 20): array
    {
        global $wpdb;

        $table = $wpdb->prefix . 'tma_activity_logs';
        $query = $wpdb->prepare(
            "SELECT * FROM {$table} WHERE user_id = %d AND workspace_id = %d ORDER BY created_at DESC LIMIT %d",
            $userId,
            $workspaceId,
            $limit
        );

        return $wpdb->get_results($query, ARRAY_A) ?: [];
    }
}
