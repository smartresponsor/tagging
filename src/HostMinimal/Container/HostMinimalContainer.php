<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\HostMinimal\Container;

final class HostMinimalContainer
{
    /** @var array<string, callable():mixed> */
    private array $entries = [];

    public function share(string $id, callable $factory): void
    {
        $this->entries[$id] = self::sharedEntry($factory);
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
        return ($this->entry($id, 'Unknown container entry: %s'))();
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
            $export[$id] = $this->entry($id, 'Cannot export unknown container entry: %s');
        }

        return $export;
    }

    private static function sharedEntry(callable $factory): callable
    {
        $resolved = false;
        $instance = null;

        return static function () use (&$resolved, &$instance, $factory) {
            if (!$resolved) {
                $instance = $factory();
                $resolved = true;
            }

            return $instance;
        };
    }

    private function entry(string $id, string $messageTemplate): callable
    {
        if (!isset($this->entries[$id])) {
            throw new \RuntimeException(sprintf($messageTemplate, $id));
        }

        return $this->entries[$id];
    }
}
