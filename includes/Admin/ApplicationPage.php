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
        <div class="wrap tma-admin-page">
            <h1>Task Management App</h1>
            <?php echo (new \TMA\TaskManagement\Frontend\ApplicationRenderer())->render(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
        </div>
        <?php
    }

    public function enqueueAssets(): void
    {
        $screen = get_current_screen();
        if (!$screen || 'toplevel_page_tma-task-management' !== $screen->id) {
            return;
        }

        $adminStyle = TMA_TASK_MANAGEMENT_APP_PATH . 'assets/admin.css';
        wp_enqueue_style(
            'tma-admin-style',
            plugins_url('/assets/admin.css', TMA_TASK_MANAGEMENT_APP_FILE),
            [],
            file_exists($adminStyle) ? (string) filemtime($adminStyle) : '1.0.0'
        );

        \TMA\TaskManagement\Core\Plugin::instance()->enqueueApplicationAssets('admin');
    }
}
