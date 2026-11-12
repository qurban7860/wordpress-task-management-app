<?php

namespace TMA\TaskManagement\Frontend;

final class ApplicationRenderer
{
    public function render(bool $withSiteChrome = false): string
    {
        $loggedIn = is_user_logged_in();
        $loginUrl = wp_login_url(get_permalink());
        $content = '';

        if (!$loggedIn) {
            $content = sprintf(
                '<div class="tma-login-required" id="tma-login" role="status"><h2>%1$s</h2><p>%2$s</p><a class="tma-login-link" href="%3$s">%4$s</a></div>',
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
                        <nav class="tma-site-nav" aria-label="%3$s">
                            <a class="tma-site-nav__link" href="#tma-app-overview">%4$s</a>
                            <a class="tma-site-nav__link" href="%5$s">%6$s</a>
                            <a class="tma-site-nav__cta" href="%7$s">%8$s</a>
                        </nav>
                    </div>
                </header>
                <div class="tma-site-main">
                    <section class="tma-app-intro" id="tma-app-overview" aria-labelledby="tma-app-intro-title">
                        <div class="tma-app-intro__copy">
                            <p class="tma-app-intro__eyebrow">%9$s</p>
                            <h1 id="tma-app-intro-title">%10$s</h1>
                            <p class="tma-app-intro__description">%11$s</p>
                        </div>
                        <p class="tma-app-intro__note"><span class="tma-app-intro__status" aria-hidden="true"></span>%12$s</p>
                    </section>
                    %13$s
                </div>
                <footer class="tma-site-footer">
                    <div class="tma-site-footer__inner">
                        <span>%14$s</span>
                        <span>%15$s</span>
                    </div>
                </footer>
            </div>',
            esc_url(home_url('/')),
            esc_html__('Task Manager', 'task-management-app'),
            esc_attr__('Primary navigation', 'task-management-app'),
            esc_html__('Overview', 'task-management-app'),
            $loggedIn ? '#tma-app-root' : '#tma-login',
            esc_html__('Workspace', 'task-management-app'),
            $loggedIn ? '#tma-app-root' : esc_url($loginUrl),
            $loggedIn ? esc_html__('Go to workspace', 'task-management-app') : esc_html__('Sign in', 'task-management-app'),
            esc_html__('A calmer way to get things done', 'task-management-app'),
            esc_html__('Plan your work. Focus on what matters.', 'task-management-app'),
            esc_html__('Capture tasks, set priorities and due dates, and review recent activity—all in private workspaces built around the way you work.', 'task-management-app'),
            esc_html__('Private workspaces. Clear next steps.', 'task-management-app'),
            $content, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            esc_html(sprintf(
                /* translators: %s: current year */
                __('© %s Task Manager', 'task-management-app'),
                wp_date('Y')
            )),
            esc_html__('Your work stays private to your account.', 'task-management-app')
        );
    }
}
