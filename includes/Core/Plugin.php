<?php

namespace TMA\TaskManagement\Core;

use TMA\TaskManagement\Admin\ApplicationPage;
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

        add_action('admin_menu', [$this, 'registerAdminPage']);
        add_action('admin_enqueue_scripts', [$this, 'enqueueAdminAssets']);
        add_action('rest_api_init', [$this, 'registerRestRoutes']);
        add_action('plugins_loaded', [$this, 'initializePlugin']);
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

    private function initializeDefaultWorkspaceForExistingUsers(): void
    {
        if (!is_user_logged_in()) {
            return;
        }

        $workspaceService = new WorkspaceService(new WorkspaceRepository(), new \TMA\TaskManagement\Security\Validator());
        $workspaceService->ensureDefaultWorkspacesForUser(get_current_user_id());
    }
}
