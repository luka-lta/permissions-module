<?php

declare(strict_types=1);

namespace PermissionsModule\Value;

use Countable;
use Generator;
use IteratorAggregate;
use JsonSerializable;

class Roles implements IteratorAggregate, JsonSerializable, Countable
{
    private readonly array $roles;

    private function __construct(Role ...$roles)
    {
        $this->roles = $roles;
    }

    public static function from(Role ...$roles): self
    {
        return new self(...$roles);
    }

    public function getIterator(): Generator
    {
        yield from $this->roles;
    }

    public function count(): int
    {
        return count($this->roles);
    }

    public function toArray(): array
    {
        return array_map(static fn($role) => $role->toArray(), $this->roles);
    }

    public function jsonSerialize(): array
    {
        return $this->roles;
    }
}
