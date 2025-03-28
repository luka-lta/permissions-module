<?php

declare(strict_types=1);

namespace PermissionsModule\Repository;

use PDO;
use PermissionsModule\Value\Permission;
use PermissionsModule\Value\Permissions;
use PermissionsModule\Value\Role;
use PermissionsModule\Value\Roles;

class PermissionsRepsitory implements PermissionRepositoryInterface
{
    public function __construct(
        private readonly PDO $pdo,
    ) {
    }

    public function getPermissionsForUser(int $userId): Permissions
    {
        $stmt = $this->pdo->prepare("
            SELECT p.permission_id, p.permission_name, p.permission_description
            FROM permissions p
            JOIN role_permissions rp ON p.permission_id = rp.permission_id
            JOIN user_roles ur ON rp.role_id = ur.role_id
            WHERE ur.user_id = :user_id
        ");
        $stmt->execute(['user_id' => $userId]);

        $permissions = [];
        foreach ($stmt as $row) {
            $permissions[] = Permission::fromDatabase($row);
        }

        return Permissions::fromObjects(...$permissions);
    }

    public function getPermissionsForRole(int $roleId): Permissions
    {
        $stmt = $this->pdo->prepare("
            SELECT p.permission_id, p.permission_name, p.permission_description
            FROM permissions p
            JOIN role_permissions rp ON p.permission_id = rp.permission_id
            WHERE rp.role_id = :role_id
        ");
        $stmt->execute(['role_id' => $roleId]);

        $permissions = [];
        foreach ($stmt as $row) {
            $permissions[] = Permission::fromDatabase($row);
        }

        return Permissions::fromObjects(...$permissions);
    }

    public function getRolesForUser(int $userId): Roles
    {
        $stmt = $this->pdo->prepare("
            SELECT r.id, r.name 
            FROM roles r
            JOIN user_roles ur ON r.id = ur.role_id
            WHERE ur.user_id = :user_id
        ");
        $stmt->execute(['user_id' => $userId]);

        $roles = [];
        foreach ($stmt as $row) {
            $roles[] = Role::fromDatabase($row);
        }

        return Roles::from(...$roles);
    }

    public function userHasPermission(int $userId, string $permission): bool
    {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) as count 
            FROM permissions p
            JOIN role_permissions rp ON p.id = rp.permission_id
            JOIN user_roles ur ON rp.role_id = ur.role_id
            WHERE ur.user_id = :user_id AND p.name = :permission
        ");
        $stmt->execute([
            'user_id' => $userId,
            'permission' => $permission
        ]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'] > 0;
    }

    public function roleHasPermission(int $roleId, string $permission): bool
    {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) as count 
            FROM permissions p
            JOIN role_permissions rp ON p.id = rp.permission_id
            WHERE rp.role_id = :role_id AND p.name = :permission
        ");
        $stmt->execute([
            'role_id' => $roleId,
            'permission' => $permission
        ]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'] > 0;
    }
}
