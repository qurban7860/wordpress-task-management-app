<?php

namespace TMA\TaskManagement\REST;

use TMA\TaskManagement\Database\ActivityRepository;
use TMA\TaskManagement\Database\WorkspaceRepository;
use TMA\TaskManagement\Services\ActivityService;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

final class ActivityController
{
    public function registerRoutes(): void
    {
        register_rest_route('tma/v1', '/activity', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [$this, 'getActivity'],
            'permission_callback' => function () {
                return is_user_logged_in();
            },
        ]);
    }

    public function getActivity(WP_REST_Request $request): WP_REST_Response
    {
        $workspaceId = (int) ($request->get_param('workspace_id') ?? 0);
        $service = new ActivityService(new ActivityRepository(), new WorkspaceRepository());

        return rest_ensure_response([
            'success' => true,
            'activity' => $service->getForWorkspace($workspaceId),
        ]);
    }
}
