<?php

namespace TMA\TaskManagement\Core;

use TMA\TaskManagement\Admin\ApplicationPage;
use TMA\TaskManagement\Frontend\ApplicationRenderer;
use TMA\TaskManagement\Database\WorkspaceRepository;
use TMA\TaskManagement\REST\ActivityController;
use TMA\TaskManagement\REST\TaskController;
use TMA\TaskManagement\REST\WorkspaceController;
use TMA\TaskManagement\Services\WorkspaceService;

final class Plugin
{
    private static ?self $instance = null;

    private function __construct()
    {
        $this->bootstrap();
    }

    public static function instance(): self
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function activate(): void
    {
        $this->ensureDatabase();
        $this->initializeDefaultWorkspaceForExistingUsers();
    }

    public function deactivate(): void
    {
    }

    private function bootstrap(): void
    {
        require_once __DIR__ . '/../Database/Database.php';
        require_once __DIR__ . '/../Database/WorkspaceRepository.php';
        require_once __DIR__ . '/../Database/TaskRepository.php';
        require_once __DIR__ . '/../Database/ActivityRepository.php';
        require_once __DIR__ . '/../Security/Validator.php';
        require_once __DIR__ . '/../Security/Permissions.php';
        require_once __DIR__ . '/../Services/WorkspaceService.php';
        require_once __DIR__ . '/../Services/TaskService.php';
        require_once __DIR__ . '/../Services/ActivityService.php';
        require_once __DIR__ . '/../REST/WorkspaceController.php';
        require_once __DIR__ . '/../REST/TaskController.php';
        require_once __DIR__ . '/../REST/ActivityController.php';
        require_once __DIR__ . '/../Admin/ApplicationPage.php';
        require_once __DIR__ . '/../Frontend/ApplicationRenderer.php';

        add_action('admin_menu', [$this, 'registerAdminPage']);
        add_action('admin_enqueue_scripts', [$this, 'enqueueAdminAssets']);
        add_action('wp_enqueue_scripts', [$this, 'enqueueFrontendAssets']);
        add_filter('body_class', [$this, 'addFrontendBodyClass']);
        add_filter('pre_get_document_title', [$this, 'filterFrontendDocumentTitle']);
        add_action('rest_api_init', [$this, 'registerRestRoutes']);
        add_action('plugins_loaded', [$this, 'initializePlugin']);
        add_shortcode('tma_app', [$this, 'renderShortcode']);
    }

    public function initializePlugin(): void
    {
        if (is_admin() && current_user_can('manage_options')) {
            $this->ensureDatabase();
            $this->initializeDefaultWorkspaceForExistingUsers();
        }
    }

    public function ensureDatabase(): void
    {
        $database = new \TMA\TaskManagement\Database\Database();
        $database->createTables();
    }

    public function registerRestRoutes(): void
    {
        $workspaceController = new WorkspaceController();
        $taskController = new TaskController();
        $activityController = new ActivityController();

        $workspaceController->registerRoutes();
        $taskController->registerRoutes();
        $activityController->registerRoutes();
    }

    public function registerAdminPage(): void
    {
        $page = new ApplicationPage();
        $page->register();
    }

    public function enqueueAdminAssets(): void
    {
        $page = new ApplicationPage();
        $page->enqueueAssets();
    }

    public function enqueueFrontendAssets(): void
    {
        if (!is_singular()) {
            return;
        }

        $post = get_post();
        if (!$post || !has_shortcode($post->post_content, 'tma_app')) {
            return;
        }

        $this->enqueueApplicationAssets('frontend');
    }

    public function renderShortcode(): string
    {
        $this->enqueueApplicationAssets('frontend');

        return (new ApplicationRenderer())->render(true);
    }

    public function addFrontendBodyClass(array $classes): array
    {
        if (is_singular()) {
            $post = get_post();
            if ($post && has_shortcode($post->post_content, 'tma_app')) {
                $classes[] = 'tma-app-page';
            }
        }

        return $classes;
    }

    public function filterFrontendDocumentTitle(string $title): string
    {
        if (!is_singular()) {
            return $title;
        }

        $post = get_post();
        if (!$post || !has_shortcode($post->post_content, 'tma_app')) {
            return $title;
        }

        return __('Task Manager', 'task-management-app');
    }

    public function enqueueApplicationAssets(string $context): void
    {
        $styleFile = TMA_TASK_MANAGEMENT_APP_PATH . 'assets/app.css';
        $scriptFile = TMA_TASK_MANAGEMENT_APP_PATH . 'assets/admin.js';
        $styleVersion = file_exists($styleFile) ? (string) filemtime($styleFile) : '1.0.0';
        $scriptVersion = file_exists($scriptFile) ? (string) filemtime($scriptFile) : '1.0.0';
        $styleHandle = 'tma-' . $context . '-app';
        $scriptHandle = 'tma-' . $context . '-app';

        wp_enqueue_style(
            $styleHandle,
            plugins_url('/assets/app.css', TMA_TASK_MANAGEMENT_APP_FILE),
            [],
            $styleVersion
        );
        wp_enqueue_script(
            $scriptHandle,
            plugins_url('/assets/admin.js', TMA_TASK_MANAGEMENT_APP_FILE),
            [],
            $scriptVersion,
            true
        );
        wp_localize_script($scriptHandle, 'tmaAppData', [
            'restUrl' => esc_url_raw(rest_url('tma/v1')),
            'nonce' => wp_create_nonce('wp_rest'),
            'loginUrl' => esc_url_raw(wp_login_url(is_singular() ? get_permalink() : '')),
        ]);
    }

    private function initializeDefaultWorkspaceForExistingUsers(): void
    {
        if (!is_user_logged_in()) {
            return;
        }

        $workspaceService = new WorkspaceService(
            new WorkspaceRepository(),
            new \TMA\TaskManagement\Security\Validator(),
            new \TMA\TaskManagement\Database\TaskRepository(),
            new \TMA\TaskManagement\Database\ActivityRepository()
        );
        $workspaceService->ensureDefaultWorkspacesForUser(get_current_user_id());
    }
}
