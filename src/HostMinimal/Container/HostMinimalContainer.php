<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\HostMinimal\Container;

final class HostMinimalContainer
{
    /** @var array<string, callable():mixed> */
    private array $factories = [];

    /** @var array<string, callable():mixed> */
    private array $entries = [];

    public function share(string $id, callable $factory): void
    {
        $this->factories[$id] = $factory;
        $resolved = false;
        $instance = null;

        $this->entries[$id] = static function () use (&$resolved, &$instance, $factory) {
            if ($resolved) {
                return $instance;
            }

            $instance = $factory();
            $resolved = true;

            return $instance;
        };
    }

    public function value(string $id, mixed $value): void
    {
        $this->entries[$id] = static fn(): mixed => $value;
    }

    public function has(string $id): bool
    {
        return isset($this->entries[$id]);
    }

    public function get(string $id): mixed
    {
        if (!$this->has($id)) {
            throw new \RuntimeException(sprintf('Unknown container entry: %s', $id));
        }

        return ($this->entries[$id])();
    }

    /**
     * @param list<string>|null $ids
     *
     * @return array<string, callable():mixed>
     */
    public function export(?array $ids = null): array
    {
        if (null === $ids) {
            return $this->entries;
        }

        $export = [];

        foreach ($ids as $id) {
            if (!$this->has($id)) {
                throw new \RuntimeException(sprintf('Cannot export unknown container entry: %s', $id));
            }

            $export[$id] = $this->entries[$id];
        }

        return $export;
    }
}
