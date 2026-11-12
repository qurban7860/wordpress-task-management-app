<?php

namespace TMA\TaskManagement\Frontend;

final class ApplicationRenderer
{
    public function render(bool $withSiteChrome = false): string
    {
        $content = '';

        if (!is_user_logged_in()) {
            $loginUrl = wp_login_url(get_permalink());

            $content = sprintf(
                '<div class="tma-login-required" role="status"><h2>%1$s</h2><p>%2$s</p><a class="tma-login-link" href="%3$s">%4$s</a></div>',
                esc_html__('Sign in to manage your tasks', 'task-management-app'),
                esc_html__('Your workspaces and tasks are private to your account. Sign in to continue.', 'task-management-app'),
                esc_url($loginUrl),
                esc_html__('Sign in', 'task-management-app')
            );
        } else {
            $content = '<div class="tma-app" id="tma-app-root" aria-label="' .
                esc_attr__('Task management application', 'task-management-app') .
                '"><p class="tma-app-loading" role="status">' .
                esc_html__('Loading your workspaces…', 'task-management-app') .
                '</p></div>';
        }

        if (!$withSiteChrome) {
            return $content;
        }

        return sprintf(
            '<div class="tma-site-frame">
                <header class="tma-site-header">
                    <div class="tma-site-header__inner">
                        <a class="tma-site-brand" href="%1$s">
                            <span class="tma-site-brand__mark" aria-hidden="true">T</span>
                            <span class="tma-site-brand__name">%2$s</span>
                        </a>
                        <span class="tma-site-header__tagline">%3$s</span>
                    </div>
                </header>
                <div class="tma-site-main">%4$s</div>
                <footer class="tma-site-footer">
                    <div class="tma-site-footer__inner">
                        <span>%5$s</span>
                        <span>%6$s</span>
                    </div>
                </footer>
            </div>',
            esc_url(home_url('/')),
            esc_html__('Task Manager', 'task-management-app'),
            esc_html__('A clear space for your work', 'task-management-app'),
            $content, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            esc_html(sprintf(
                /* translators: %s: current year */
                __('© %s Task Manager', 'task-management-app'),
                wp_date('Y')
            )),
            esc_html__('Your tasks are private to your account.', 'task-management-app')
        );
    }
}
