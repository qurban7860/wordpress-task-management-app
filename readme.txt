=== Task Management App ===
Contributors: Trickle Up
Tags: tasks, management, productivity, dashboard
Requires at least: 6.4
Tested up to: 6.7
Requires PHP: 8.2
Stable tag: 1.0.0

A WordPress plugin for task management with private workspaces, task tracking, and activity logs.

== Description ==

Task Management App provides a responsive dashboard for managing private workspaces and tasks from WordPress Admin or a frontend page.
The frontend shortcode includes responsive app navigation, a product introduction, and branded footer scoped to its page; the active theme and other site pages are unchanged. The WordPress admin toolbar is hidden on this application page for a clean, signed-in experience.

== Frontend setup ==

1. In WordPress Admin, go to Pages > Add New and create a page named Task Manager.
2. Add a Shortcode block in the block editor and enter [tma_app].
3. Publish the page.
4. To use the app as the site homepage, go to Settings > Reading, select A static page, and choose Task Manager as the Homepage.

The plugin does not change the site's homepage or page settings when activated. Visitors must sign in to access task data. Each user's workspaces and tasks are private to that account.

== Installation ==

1. Upload the plugin folder to the /wp-content/plugins/ directory.
2. Activate the plugin via the WordPress admin panel.
3. Navigate to Task Management in the admin menu.
