<?php

declare(strict_types=1);

namespace PermissionsModule\Repository;

use PDO;
use PDOException;
use PermissionsModule\Exception\PermissionDatabaseException;
use PermissionsModule\Value\Permissions;
use PermissionsModule\Value\Role;
use PermissionsModule\Value\Roles;

class RoleRepository
{
    public function __construct(
        private readonly PDO $pdo,
    ) {
    }

    public function getAvailableRoles(): Roles
    {
        $sql = <<<SQL
            SELECT 
                r.role_id,
                r.role_name,
                COALESCE(
                NULLIF(
                    JSON_ARRAYAGG(
                        JSON_OBJECT(
                            'permission_id', p.permission_id,
                            'permission_name', p.permission_name,
                            'permission_description', p.permission_description
                        )
                    ),
                JSON_ARRAY(NULL)
                ),
                JSON_ARRAY()
                ) AS permissions
            FROM 
                user_roles r
            LEFT JOIN 
                role_permissions rp ON r.role_id = rp.role_id
            LEFT JOIN 
                permissions p ON rp.permission_id = p.permission_id
            GROUP BY 
                r.role_id, r.role_name
        SQL;

        try {
            $statement = $this->pdo->prepare($sql);
            $statement->execute();

            $roles = [];
            foreach ($statement as $row) {
                $roles[] = Role::fromDatabase($row);
            }
        } catch (PDOException $exception) {
            throw new PermissionDatabaseException('Failed to retrieve roles', previous: $exception);
        }

        return Roles::from(...$roles);
    }

    public function addPermissionToRole(int $roleId, Permissions $permissions): void
    {
        $sql = <<<SQL
            INSERT INTO role_permissions (role_id, permission_id)
            VALUES (:role_id, :permission_id)
        SQL;

        $this->pdo->beginTransaction();

        try {
            foreach ($permissions as $permission) {
                $statement = $this->pdo->prepare($sql);
                $statement->execute([
                    'role_id' => $roleId,
                    'permission_id' => $permission->getPermissionId(),
                ]);
            }

            $this->pdo->commit();
        } catch (PDOException $exception) {
            $this->pdo->rollBack();
            throw new PermissionDatabaseException('Failed to add permission to role', previous: $exception);
        }
    }

    public function removePermissionFromRole(int $roleId, Permissions $permissions): void
    {
        $sql = <<<SQL
            DELETE FROM role_permissions
            WHERE role_id = :role_id
            AND permission_id = :permission_id
        SQL;

        $this->pdo->beginTransaction();
        try {
            foreach ($permissions as $permission) {
                $statement = $this->pdo->prepare($sql);
                $statement->execute([
                    'role_id' => $roleId,
                    'permission_id' => $permission->getPermissionId(),
                ]);
            }
            $this->pdo->commit();
        } catch (PDOException $exception) {
            $this->pdo->rollBack();
            throw new PermissionDatabaseException('Failed to remove permission from role', previous: $exception);
        }
    }

    public function createRole(string $roleName, Permissions $permissions): Role
    {
        $sql = <<<SQL
            INSERT INTO user_roles (role_name)
            VALUES (:role_name)
        SQL;

        $sql2 = <<<SQL
            INSERT INTO role_permissions (role_id, permission_id)
            VALUES (:role_id, :permission_id)
        SQL;

        $this->pdo->beginTransaction();
        try {
            $statement = $this->pdo->prepare($sql);
            $statement->execute([
                'role_name' => $roleName,
            ]);

            $roleId = (int)$this->pdo->lastInsertId();
            foreach ($permissions as $permission) {
                $statement = $this->pdo->prepare($sql2);
                $statement->execute([
                    'role_id' => $roleId,
                    'permission_id' => $permission->getPermissionId(),
                ]);
            }

            $this->pdo->commit();
        } catch (PDOException $exception) {
            $this->pdo->rollBack();
            throw new PermissionDatabaseException('Failed to create role', previous: $exception);
        }

        return Role::create($roleName, $permissions, $roleId);
    }

    public function updateRole(Role $role): void
    {
        $sql = <<<SQL
            UPDATE user_roles
            SET role_name = :role_name
            WHERE role_id = :role_id
        SQL;

        try {
            $statement = $this->pdo->prepare($sql);
            $statement->execute([
                'role_name' => $role->getRoleName(),
                'role_id' => $role->getRoleId(),
            ]);
        } catch (PDOException $exception) {
            throw new PermissionDatabaseException('Failed to update role', previous: $exception);
        }
    }

    public function getRoleById(int $roleId): Role
    {
        $sql = <<<SQL
            SELECT 
                r.role_id,
                r.role_name,
                COALESCE(
                NULLIF(
                    JSON_ARRAYAGG(
                        JSON_OBJECT(
                            'permission_id', p.permission_id,
                            'permission_name', p.permission_name,
                            'permission_description', p.permission_description
                        )
                    ),
                JSON_ARRAY(NULL)
                ),
                JSON_ARRAY()
                ) AS permissions
            FROM 
                user_roles r
            LEFT JOIN 
                role_permissions rp ON r.role_id = rp.role_id
            LEFT JOIN 
                permissions p ON rp.permission_id = p.permission_id
            WHERE 
                r.role_id = :role_id
            GROUP BY 
                r.role_id, r.role_name
        SQL;

        try {
            $statement = $this->pdo->prepare($sql);
            $statement->execute([
                'role_id' => $roleId,
            ]);

            $row = $statement->fetch();
            if ($row === false) {
                throw new PermissionDatabaseException('Role not found');
            }

            return Role::fromDatabase($row);
        } catch (PDOException $exception) {
            throw new PermissionDatabaseException('Failed to retrieve role', previous: $exception);
        }
    }
}
