<?php

namespace TMA\TaskManagement\Admin;

final class ApplicationPage
{
    public function register(): void
    {
        add_menu_page(
            'Task Management App',
            'Task Management',
            'read',
            'tma-task-management',
            [$this, 'render'],
            'dashicons-clipboard',
            26
        );
    }

    public function render(): void
    {
        ?>
        <div class="wrap">
            <h1>Task Management App</h1>
            <div id="tma-app-root"></div>
        </div>
        <?php
    }

    public function enqueueAssets(): void
    {
        $screen = get_current_screen();
        if (!$screen || 'toplevel_page_tma-task-management' !== $screen->id) {
            return;
        }

        wp_enqueue_style(
            'tma-admin-style',
            plugins_url('/assets/admin.css', TMA_TASK_MANAGEMENT_APP_FILE),
            [],
            '1.0.0'
        );

        wp_enqueue_script(
            'tma-admin-app',
            plugins_url('/assets/admin.js', TMA_TASK_MANAGEMENT_APP_FILE),
            [],
            '1.0.0',
            true
        );

        wp_localize_script('tma-admin-app', 'tmaAppData', [
            'restUrl' => rest_url('tma/v1'),
            'nonce' => wp_create_nonce('wp_rest'),
            'userId' => get_current_user_id(),
        ]);
    }
}
