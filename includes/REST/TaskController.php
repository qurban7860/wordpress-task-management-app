<?php

namespace TMA\TaskManagement\REST;

use TMA\TaskManagement\Database\ActivityRepository;
use TMA\TaskManagement\Database\TaskRepository;
use TMA\TaskManagement\Security\Permissions;
use TMA\TaskManagement\Security\Validator;
use TMA\TaskManagement\Services\TaskService;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

final class TaskController
{
    public function registerRoutes(): void
    {
        register_rest_route('tma/v1', '/tasks', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [$this, 'getTasks'],
            'permission_callback' => function () {
                return is_user_logged_in();
            },
        ]);

        register_rest_route('tma/v1', '/tasks', [
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => [$this, 'createTask'],
            'permission_callback' => function () {
                return is_user_logged_in();
            },
        ]);

        register_rest_route('tma/v1', '/tasks/(?P<id>\d+)', [
            'methods' => [WP_REST_Server::EDITABLE, WP_REST_Server::DELETABLE],
            'callback' => [$this, 'updateOrDeleteTask'],
            'permission_callback' => function () {
                return is_user_logged_in();
            },
        ]);
    }

    public function getTasks(WP_REST_Request $request): WP_REST_Response
    {
        $workspaceId = (int) ($request->get_param('workspace_id') ?? 0);
        $filters = [
            'status' => $request->get_param('status'),
            'priority' => $request->get_param('priority'),
            'search' => $request->get_param('search'),
            'sort' => $request->get_param('sort'),
            'order' => $request->get_param('order'),
        ];
        $service = new TaskService(new TaskRepository(), new ActivityRepository(), new Validator(), new Permissions());

        return rest_ensure_response([
            'success' => true,
            'tasks' => $service->getTasksForWorkspace($workspaceId, $filters),
        ]);
    }

    public function createTask(WP_REST_Request $request): WP_REST_Response
    {
        $payload = $request->get_json_params();
        $workspaceId = (int) ($payload['workspace_id'] ?? 0);
        $service = new TaskService(new TaskRepository(), new ActivityRepository(), new Validator(), new Permissions());

        return rest_ensure_response($service->create($workspaceId, $payload));
    }

    public function updateOrDeleteTask(WP_REST_Request $request): WP_REST_Response
    {
        $taskId = (int) $request->get_param('id');
        $payload = $request->get_json_params();
        $service = new TaskService(new TaskRepository(), new ActivityRepository(), new Validator(), new Permissions());

        if ($request->get_method() === 'DELETE') {
            return rest_ensure_response($service->delete($taskId));
        }

        return rest_ensure_response($service->update($taskId, $payload));
    }
}
