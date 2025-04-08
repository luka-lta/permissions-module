<?php

declare(strict_types=1);

namespace PermissionsModule\Service;

use PermissionsModule\Repository\RoleRepository;
use PermissionsModule\Value\Permissions;
use PermissionsModule\Value\Role;
use PermissionsModule\Value\Roles;

class RoleService
{
    public function __construct(
        private readonly RoleRepository $roleRepository,
        private readonly RoleCachingService $roleCachingService,
    ) {
    }

    public function getDefaultRole(): Role
    {
        return $this->roleRepository->getRoleById(1);
    }

    public function getAvailableRoles(): Roles
    {
        return $this->roleRepository->getAvailableRoles();
    }

    public function addPermissionToRole(int $roleId, Permissions $newPermission): void
    {
        $this->getRoleById($roleId);

        $this->roleRepository->addPermissionToRole($roleId, $newPermission);
    }

    public function removePermissionFromRole(int $roleId, Permissions $permissions): void
    {
        $this->roleRepository->removePermissionFromRole($roleId, $permissions);
    }

    public function getRoleById(int $roleId): Role
    {
        $caching = $this->roleCachingService->getRole($roleId);

        if ($caching !== null) {
            return $caching;
        }

        $role = $this->roleRepository->getRoleById($roleId);
        $this->roleCachingService->cacheRole($role);
        return $role;
    }

    public function updateRole(Role $role): void
    {
        $this->roleCachingService->cacheRole($role);
        $this->roleRepository->updateRole($role);
    }
}
