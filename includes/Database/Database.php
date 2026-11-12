<?php

namespace TMA\TaskManagement\Database;

final class Database
{
    public function createTables(): void
    {
        global $wpdb;

        $charsetCollate = $wpdb->get_charset_collate();
        $workspaceTable = $wpdb->prefix . 'tma_workspaces';
        $taskTable = $wpdb->prefix . 'tma_tasks';
        $activityTable = $wpdb->prefix . 'tma_activity_logs';

        $sql = "CREATE TABLE {$workspaceTable} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id BIGINT UNSIGNED NOT NULL,
            name VARCHAR(255) NOT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            PRIMARY KEY  (id),
            KEY user_id (user_id),
            KEY name (name)
        ) {$charsetCollate};

        CREATE TABLE {$taskTable} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id BIGINT UNSIGNED NOT NULL,
            workspace_id BIGINT UNSIGNED NOT NULL,
            title VARCHAR(255) NOT NULL,
            description LONGTEXT NULL,
            priority VARCHAR(20) NOT NULL DEFAULT 'medium',
            status VARCHAR(20) NOT NULL DEFAULT 'todo',
            due_date DATE NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            PRIMARY KEY  (id),
            KEY user_id (user_id),
            KEY workspace_id (workspace_id),
            KEY status (status),
            KEY priority (priority),
            KEY due_date (due_date)
        ) {$charsetCollate};

        CREATE TABLE {$activityTable} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id BIGINT UNSIGNED NOT NULL,
            workspace_id BIGINT UNSIGNED NOT NULL,
            task_id BIGINT UNSIGNED NULL,
            action_type VARCHAR(50) NOT NULL,
            description LONGTEXT NOT NULL,
            created_at DATETIME NOT NULL,
            PRIMARY KEY  (id),
            KEY user_id (user_id),
            KEY workspace_id (workspace_id),
            KEY task_id (task_id),
            KEY action_type (action_type),
            KEY created_at (created_at)
        ) {$charsetCollate};";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }
}
