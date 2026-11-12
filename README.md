# Task Management App

A self-hosted task manager for WordPress. It adds a responsive workspace and task dashboard to WordPress Admin and to any page containing the `[tma_app]` shortcode. The frontend is styled as an app page while leaving the active WordPress theme and unrelated pages in place.

## What the plugin provides

- **Private workspaces:** each account has Personal and Work workspaces, and can create, rename, and delete additional workspaces. Personal and Work are protected defaults.
- **Task management:** create, edit, and delete tasks with a title, description, priority, status, and optional due date.
- **Find and organize work:** search tasks, filter by status and priority at the same time, and sort by due date, creation date, title, priority, or status.
- **Recent activity:** review recent task activity within the selected workspace.
- **Responsive interface:** use the application on desktop, tablet, and mobile.
- **WordPress integration:** open the application from the Task Management admin menu or embed it on a frontend page with `[tma_app]`.
- **Account-based privacy:** task, workspace, and activity access requires a signed-in WordPress account and is scoped to the current user. Logged-out visitors see a sign-in prompt instead of task data.

The plugin does not include billing, team sharing, email notifications, data export/import, or a separate user-management system. WordPress remains responsible for accounts, sign-in, and password recovery.

## Requirements

The plugin metadata declares:

- WordPress **6.4 or newer** (tested through **6.7**).
- PHP **8.2 or newer**.
- A working WordPress database connection (MySQL or a compatible database supported by the WordPress installation).
- A WordPress site where users can sign in. Use HTTPS for production.

The plugin has no separate Node.js service, external API account, or frontend build step. Its CSS and JavaScript ship with the plugin.

## Install the plugin

### Install from a ZIP file

1. Obtain the plugin ZIP from the site or release where it was distributed. The ZIP must contain a `task-management-app` plugin folder with `task-management-app.php` at its root.
2. In WordPress Admin, open **Plugins → Add New Plugin → Upload Plugin**.
3. Choose the ZIP file, select **Install Now**, and then **Activate Plugin**.
4. Confirm **Task Management** appears in the Admin menu.

### Install by copying the plugin folder

1. Copy the complete `task-management-app` folder into `wp-content/plugins/` on the WordPress server.
2. In WordPress Admin, open **Plugins** and activate **Task Management App**.
3. Confirm **Task Management** appears in the Admin menu.

Activation creates or updates the plugin's workspace, task, and activity tables. It does **not** create a page or change the site's homepage setting.

## Create the frontend application page

1. In WordPress Admin, open **Pages → Add New Page**.
2. Set the title to **Task Manager**.
3. Add a **Shortcode** block using the block editor's **+** button.
4. Enter exactly:

   ```text
   [tma_app]
   ```

5. Publish the page.
6. Open **View Page** and confirm the Task Manager application appears.

The shortcode renders the existing application; it does not create a second task system. Its branded app navigation, introduction, and footer and its page-specific styling are only shown on pages containing the shortcode. The existing WordPress theme remains active. The WordPress admin toolbar is hidden on the shortcode page for signed-in users, but remains available in Admin and on other pages.

## Optional: use Task Manager as the homepage

Only change the homepage if you want visitors to see the task application at the site's root URL.

1. Open **Settings → Reading**.
2. Under **Your homepage displays**, select **A static page**.
3. Select **Task Manager** under **Homepage**.
4. Leave **Posts page** unselected if the site does not need a blog index.
5. Select **Save Changes**.
6. Visit the site's root URL and verify the app appears.

You can keep the task page at its own URL and leave the current homepage unchanged instead. Plugin activation and deactivation do not alter Reading settings.

## Set up user access

1. In WordPress Admin, open **Users → Add New User** and create an account for each person who needs access.
2. Assign an appropriate WordPress role. Users must be able to sign in; the plugin does not create accounts or grant access to logged-out visitors.
3. Share the site's sign-in URL and the Task Manager page URL with each user.
4. Ask each person to sign in before opening the app. Each account sees and manages its own workspaces, tasks, and activity.

Do not share WordPress administrator credentials. Use individual accounts and grant only the WordPress role and capabilities appropriate for your site. The plugin does not provide collaborative workspaces: a workspace belongs to its creating user's account.

## Use the application

- Open **Task Management** in WordPress Admin, or open the published frontend page.
- Select **Personal**, **Work**, or one of your custom workspaces.
- Use **Add workspace** to create a workspace. Custom workspaces can be renamed or deleted.
- Select **New task** to create a task. Add a title, optional description, priority, status, and optional due date.
- Use the search field and status/priority filters to narrow the selected workspace's tasks. Filters can be combined.
- Choose a sort field and direction to organize the results.
- Review **Recent activity** for activity in the selected workspace.

Deleting a custom workspace permanently deletes its tasks and activity records. Personal and Work cannot be renamed or deleted through the application.

## Privacy, security, and production operations

- **Use HTTPS** and keep WordPress, PHP, the plugin, and other site software updated.
- Use unique WordPress user accounts and strong passwords. WordPress handles login and password recovery.
- The task REST API is intended for authenticated users and uses WordPress REST authentication/nonce behavior. A nonce is not a substitute for signing in or for user-scoped access checks.
- User data is stored in the site's WordPress database. Include the plugin's database tables in your normal encrypted, access-controlled backup process.
- If a page cache, CDN, or reverse proxy is enabled, exclude the Task Manager page and authenticated REST responses from public/shared caching. This app renders account-specific data and a WordPress REST nonce.
- If a security plugin or web application firewall blocks the app, check that same-origin WordPress REST requests under `/wp-json/tma/v1/` are allowed for signed-in users. Do not make the task routes public to work around a blocking rule.
- Test upgrades and configuration changes on a staging site and confirm backups before deploying to production.

### Data retention and deletion

- **Deactivate** the plugin to disable it. Deactivation leaves the plugin tables and records in place.
- Deleting a custom workspace from the app deletes that workspace's tasks and activity.
- **Permanently deleting the plugin from the WordPress Plugins screen runs its uninstall script, which drops the plugin's workspace, task, and activity tables. This permanently deletes all plugin data for all users.** Back up the database before deleting the plugin. Deactivating is not the same as deleting.

The plugin does not currently provide a built-in data export or restore screen. Restore plugin data from your WordPress database backup.

## Troubleshooting

### The homepage still shows the theme or blog

Confirm the published **Task Manager** page contains `[tma_app]`, then open **Settings → Reading** and set it as the static **Homepage**. Save the settings and clear any page cache/CDN cache.

### The shortcode appears as text, or the app is missing

Use a **Shortcode** block and enter `[tma_app]` without code fences. Confirm the plugin is active and that the page is published. Temporarily exclude this page from page caching and reload it.

### A visitor cannot see task data

The visitor must sign in to a WordPress account. Confirm the account is active, then reload the page. Logged-out access is intentionally restricted.

### Workspaces or tasks do not load

Confirm the browser can access the site's WordPress REST API and that `/wp-json/tma/v1/` requests are not blocked by a security plugin, WAF, or cache. In the browser's developer tools, inspect the failed request and WordPress server logs before changing security rules.

### Styles or scripts look stale after an update

The plugin versions its assets using their file modification times. Clear the browser, page cache, and CDN cache, then reload the Task Manager page.

## Plugin files

- `task-management-app.php` — plugin entry point and activation/deactivation hooks.
- `includes/Core/` — plugin bootstrap, shortcode, and asset registration.
- `includes/Frontend/` — shortcode rendering and sign-in experience.
- `includes/Admin/` — WordPress Admin application page.
- `includes/REST/` — authenticated task, workspace, and activity routes.
- `includes/Services/` and `includes/Database/` — application behavior and database access.
- `assets/admin.js` — shared application interactions.
- `assets/app.css` — frontend application and page-scoped presentation.
- `assets/admin.css` — Admin-specific presentation.
- `uninstall.php` — permanent data removal when the plugin is deleted.

## Compatibility notes

This release declares WordPress 6.4 as its minimum, WordPress 6.7 as its tested-through version, and PHP 8.2 as its minimum. Check the plugin header and release notes for updated compatibility information before upgrading to a different WordPress or PHP version.
