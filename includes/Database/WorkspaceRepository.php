<?php

namespace TMA\TaskManagement\Database;

final class WorkspaceRepository
{
    public function allForUser(int $userId): array
    {
        global $wpdb;

        $table = $wpdb->prefix . 'tma_workspaces';
        $query = $wpdb->prepare(
            "SELECT id, user_id, name, created_at, updated_at FROM {$table} WHERE user_id = %d ORDER BY created_at ASC, id ASC",
            $userId
        );

        return $wpdb->get_results($query, ARRAY_A) ?: [];
    }

    public function getById(int $id, int $userId): ?array
    {
        global $wpdb;

        $table = $wpdb->prefix . 'tma_workspaces';
        $query = $wpdb->prepare(
            "SELECT id, user_id, name, created_at, updated_at FROM {$table} WHERE id = %d AND user_id = %d LIMIT 1",
            $id,
            $userId
        );

        $row = $wpdb->get_row($query, ARRAY_A);

        return $row ?: null;
    }

    public function create(int $userId, string $name): int
    {
        global $wpdb;

        $table = $wpdb->prefix . 'tma_workspaces';
        $timestamp = gmdate('Y-m-d H:i:s');

        $result = $wpdb->insert(
            $table,
            [
                'user_id' => $userId,
                'name' => $name,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
            ['%d', '%s', '%s', '%s']
        );

        if (false === $result) {
            return 0;
        }

        return (int) $wpdb->insert_id;
    }

    public function update(int $id, int $userId, string $name): bool
    {
        global $wpdb;

        $table = $wpdb->prefix . 'tma_workspaces';
        $query = $wpdb->prepare(
            "UPDATE {$table} SET name = %s, updated_at = %s WHERE id = %d AND user_id = %d",
            $name,
            gmdate('Y-m-d H:i:s'),
            $id,
            $userId
        );

        return false !== $wpdb->query($query);
    }

    public function delete(int $id, int $userId): bool
    {
        global $wpdb;

        $table = $wpdb->prefix . 'tma_workspaces';
        $query = $wpdb->prepare("DELETE FROM {$table} WHERE id = %d AND user_id = %d", $id, $userId);

        return false !== $wpdb->query($query);
    }

    public function ensureDefaultWorkspaces(int $userId): array
    {
        $existing = $this->allForUser($userId);
        $names = array_map(static fn ($item) => strtolower((string) $item['name']), $existing);
        $created = [];

        foreach (['Personal', 'Work'] as $name) {
            if (in_array(strtolower($name), $names, true)) {
                continue;
            }

            $workspaceId = $this->create($userId, $name);
            if ($workspaceId > 0) {
                $created[] = $this->getById($workspaceId, $userId);
            }
        }

        if ($created !== []) {
            $existing = $this->allForUser($userId);
        }

        return $existing;
    }
}
