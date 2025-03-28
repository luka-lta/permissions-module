<?php

namespace PermissionsModule\Repository;

use PermissionsModule\Value\Permission;
use PermissionsModule\Value\Permissions;
use PermissionsModule\Value\Role;
use PermissionsModule\Value\Roles;

interface PermissionRepositoryInterface
{
    public function getPermissionsForUser(int $userId): Permissions;

    public function getPermissionsForRole(int $roleId): Permissions;

    public function getRolesForUser(int $userId): Roles;

    public function userHasPermission(int $userId, string $permission): bool;

    public function roleHasPermission(int $roleId, string $permission): bool;
}