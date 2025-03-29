<?php

declare(strict_types=1);

namespace PermissionsModule\Value;

class Role
{
    public function __construct(
        private readonly ?int $roleId,
        private string $role,
        private Permissions $permissions,
    ) {
    }

    public static function create(
        string $role,
        Permissions $permissions,
        int $roleId = null,
    ): self {
        return new self($roleId, $role, $permissions);
    }

    public static function fromDatabase(array $row): self
    {
        return new self(
            (int)$row['role_id'],
            $row['role'],
            Permissions::from(...$row['permissions']),
        );
    }

    public function getRoleId(): ?int
    {
        return $this->roleId;
    }

    public function getRoleName(): string
    {
        return $this->role;
    }

    public function getPermissions(): Permissions
    {
        return $this->permissions;
    }

    public function setPermissions(Permissions $permissions): void
    {
        $this->permissions = $permissions;
    }

    public function setRole(string $role): void
    {
        $this->role = $role;
    }

    public function toArray(): array
    {
        return [
            'roleId' => $this->roleId,
            'role' => $this->role,
            'permissions' => $this->permissions->toArray(),
        ];
    }

    public function hasPermission(Permission $permission): bool
    {
        foreach ($this->permissions as $rolePermission) {
            /** @var Permission $rolePermission */
            if ($rolePermission->getPermissionId() === $permission->getPermissionId()) {
                return true;
            }
        }

        return false;
    }

    public function hasPermissionByName(string $permissioName): bool
    {
        foreach ($this->permissions as $rolePermission) {
            /** @var Permission $rolePermission */
            if ($rolePermission->getName() === $permissioName) {
                return true;
            }
        }

        return false;
    }

    public function addPermission(Permission $permission): void
    {
        $this->permissions->merge(Permissions::fromObjects($permission));
    }
}
