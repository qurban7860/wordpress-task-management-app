<?php

namespace TMA\TaskManagement\Frontend;

final class ApplicationRenderer
{
    public function render(): string
    {
        if (!is_user_logged_in()) {
            $loginUrl = wp_login_url(get_permalink());

            return sprintf(
                '<div class="tma-login-required" role="status"><h2>%1$s</h2><p>%2$s</p><a class="tma-login-link" href="%3$s">%4$s</a></div>',
                esc_html__('Sign in to manage your tasks', 'task-management-app'),
                esc_html__('Your workspaces and tasks are private to your account. Sign in to continue.', 'task-management-app'),
                esc_url($loginUrl),
                esc_html__('Sign in', 'task-management-app')
            );
        }

        return '<div class="tma-app" id="tma-app-root" aria-label="' .
            esc_attr__('Task management application', 'task-management-app') .
            '"><p class="tma-app-loading" role="status">' .
            esc_html__('Loading your workspaces…', 'task-management-app') .
            '</p></div>';
    }
}
