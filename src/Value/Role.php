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
        $permissions = $row['permissions'];
        if (is_string($permissions)) {
            $permissions = json_decode($permissions, true, 512, JSON_THROW_ON_ERROR);
        }

        return new self(
            (int)$row['role_id'],
            $row['role_name'],
            Permissions::from($permissions),
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

    public function hasPermissionByName(string $permissionName): bool
    {
        foreach ($this->permissions as $rolePermission) {
            /** @var Permission $rolePermission */
            if ($rolePermission->getName() === $permissionName) {
                return true;
            }
        }

        return false;
    }

    public function addPermission(Permission $permission): void
    {
        $this->permissions = $this->permissions->merge(Permissions::fromObjects($permission));
    }
}
