=== Task Management App ===
Contributors: Trickle Up
Tags: tasks, management, productivity, dashboard
Requires at least: 6.4
Tested up to: 6.7
Requires PHP: 8.2
Stable tag: 1.0.0

A WordPress plugin for task management with private workspaces, task tracking, and activity logs.

== Description ==

Task Management App adds a responsive, private task dashboard to WordPress Admin or a frontend page. Manage workspaces and tasks, organize work with search, simultaneous status and priority filters, sorting, due dates, and review recent activity.

== Frontend setup ==

1. Create a WordPress page named Task Manager.
2. Add [tma_app] using a Shortcode block and publish the page.
3. Optionally select Task Manager as the static homepage under Settings > Reading.

The plugin does not change homepage settings during activation. Visitors must sign in to access the application; user data is scoped to the signed-in account. Deactivating preserves data. Permanently deleting the plugin removes its database tables and all plugin data. Read README.md for complete installation, configuration, security, backup, and troubleshooting instructions.

== Installation ==

1. Install the plugin ZIP through Plugins > Add New Plugin > Upload Plugin, or copy its folder into /wp-content/plugins/.
2. Activate Task Management App in the WordPress Plugins screen.
3. Open Task Management in the WordPress Admin menu, or follow Frontend setup above.
