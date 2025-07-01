<?php

declare(strict_types=1);

namespace PermissionsModule\Service;

use PermissionsModule\Value\Role;
use Redis;

class RoleCachingService
{
    public function __construct(
        private readonly Redis $redis,
    ) {
    }

    public function cacheRole(Role $role): void
    {
        try {
            $data = [
                'role_id' => $role->getRoleId(),
                'role_name' => $role->getRoleName(),
                'permissions' => array_map(
                    static fn($permission) => [
                        'permission_id' => $permission->getPermissionId(),
                        'permission_name' => $permission->getName(),
                        'permission_description' => $permission->getDescription(),
                    ],
                    iterator_to_array($role->getPermissions())
                ),
            ];

            $this->redis->set('role_' . $role->getRoleId(), json_encode($data, JSON_THROW_ON_ERROR));
        } catch (\RedisException $exception) {
            return;
        }
    }

    public function getRole(int $roleId): ?Role
    {
        try {
            $role = $this->redis->get('role_' . $roleId);
            if ($role === false) {
                return null;
            }
            return Role::fromDatabase(json_decode($role, true, 512, JSON_THROW_ON_ERROR));
        } catch (\RedisException $exception) {
            return null;
        }
    }
}
