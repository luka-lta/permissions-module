<?php

declare(strict_types=1);

namespace PermissionsModule\Value;

use PermissionValidationException;

class Permission
{
    private function __construct(
        private readonly ?int $permissionId,
        private readonly string $name,
        private readonly string $description
    ) {
        if (empty($this->name)) {
            throw new PermissionValidationException('Permission name cannot be empty');
        }
    }

    public static function create(
        string $name,
        string $description
    ): self {
        return new self(null, $name, $description);
    }

    public static function fromDatabase(array $row): self
    {
        return new self(
            (int)$row['permission_id'],
            $row['permission_name'],
            $row['permission_description'],
        );
    }

    public function toArray(): array
    {
        return [
            'permissionId' => $this->permissionId,
            'name' => $this->name,
            'description' => $this->description,
        ];
    }

    public function getPermissionId(): ?int
    {
        return $this->permissionId;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function equals(Permission $other): bool
    {
        return $this->name === $other->name;
    }
}
