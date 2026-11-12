<?php
/**
 * Plugin Name: Task Management App
 * Description: A full task management application for WordPress with workspaces, tasks, activity logging, and a SaaS-style dashboard.
 * Version: 1.0.0
 * Author: Trickle Up
 * Text Domain: task-management-app
 * Requires at least: 6.4
 * Tested up to: 6.7
 * Requires PHP: 8.2
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!defined('TMA_TASK_MANAGEMENT_APP_FILE')) {
    define('TMA_TASK_MANAGEMENT_APP_FILE', __FILE__);
}

if (!defined('TMA_TASK_MANAGEMENT_APP_PATH')) {
    define('TMA_TASK_MANAGEMENT_APP_PATH', plugin_dir_path(__FILE__));
}

if (!defined('TMA_TASK_MANAGEMENT_APP_URL')) {
    define('TMA_TASK_MANAGEMENT_APP_URL', plugin_dir_url(__FILE__));
}

require_once __DIR__ . '/includes/Core/Plugin.php';

register_activation_hook(__FILE__, function () {
    TMA\TaskManagement\Core\Plugin::instance()->activate();
});

register_deactivation_hook(__FILE__, function () {
    TMA\TaskManagement\Core\Plugin::instance()->deactivate();
});

TMA\TaskManagement\Core\Plugin::instance();
