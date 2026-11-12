<?php

namespace TMA\TaskManagement\REST;

use TMA\TaskManagement\Database\WorkspaceRepository;
use TMA\TaskManagement\Security\Permissions;
use TMA\TaskManagement\Security\Validator;
use TMA\TaskManagement\Services\WorkspaceService;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

final class WorkspaceController
{
    public function registerRoutes(): void
    {
        register_rest_route('tma/v1', '/workspaces', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [$this, 'getWorkspaces'],
            'permission_callback' => function () {
                return is_user_logged_in();
            },
        ]);

        register_rest_route('tma/v1', '/workspaces', [
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => [$this, 'createWorkspace'],
            'permission_callback' => function () {
                return is_user_logged_in();
            },
        ]);
    }

    public function getWorkspaces(WP_REST_Request $request): WP_REST_Response
    {
        $service = new WorkspaceService(new WorkspaceRepository(), new Validator());

        return rest_ensure_response([
            'success' => true,
            'workspaces' => $service->getForCurrentUser(),
        ]);
    }

    public function createWorkspace(WP_REST_Request $request): WP_REST_Response
    {
        $payload = $request->get_json_params();
        $service = new WorkspaceService(new WorkspaceRepository(), new Validator());

        $result = $service->create((string) ($payload['name'] ?? ''));

        return rest_ensure_response($result);
    }
}
