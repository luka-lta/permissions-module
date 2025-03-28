<?php

declare(strict_types=1);

namespace PermissionsModule\Repository;

use Fig\Http\Message\StatusCodeInterface;
use PDO;
use PDOException;
use PermissionsModule\Exception\PermissionDatabaseException;
use PermissionsModule\Value\Permission;
use PermissionsModule\Value\Permissions;

class PermissionRepository
{
    public function __construct(
        private readonly PDO $pdo,
    ) {
    }

    public function getAvailablePermissions(): Permissions
    {
        $sql = <<<SQL
            SELECT *
            FROM permissions
        SQL;

        try {
            $stmt = $this->pdo->query($sql);

            $permissions = [];
            foreach ($stmt as $row) {
                $permissions[] = Permission::fromDatabase($row);
            }
        } catch (PDOException $exception) {
            throw new PermissionDatabaseException(
                'Failed to load permissions',
                StatusCodeInterface::STATUS_INTERNAL_SERVER_ERROR,
                $exception
            );
        }

        return Permissions::fromObjects(...$permissions);
    }
}
