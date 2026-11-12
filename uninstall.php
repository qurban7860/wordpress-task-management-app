<?php

if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

global $wpdb;

$tables = [
    $wpdb->prefix . 'tma_activity_logs',
    $wpdb->prefix . 'tma_tasks',
    $wpdb->prefix . 'tma_workspaces',
];

foreach ($tables as $table) {
    $wpdb->query("DROP TABLE IF EXISTS `{$table}`");
}

delete_option('tma_task_management_app_db_version');
