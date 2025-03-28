<?php

declare(strict_types=1);

namespace PermissionsModule\Service;

use PermissionsModule\Repository\PermissionsRepsitory;
use PermissionsModule\Value\Permission;
use PermissionsModule\Value\Permissions;
use PermissionsModule\Value\Roles;

class PermissionService
{
    public function __construct(
        private readonly PermissionsRepsitory $repository
    ) {
    }

    public function userHasPermission(int $userId, Permission $permission): bool
    {
        return $this->repository->userHasPermission($userId, $permission->getName());
    }

    public function getRolesForUser(int $userId): Roles
    {
        return $this->repository->getRolesForUser($userId);
    }

    public function getPermissionsForUser(int $userId): Permissions
    {
        return $this->repository->getPermissionsForUser($userId);
    }
}
