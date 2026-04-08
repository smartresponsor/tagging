<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

if (!function_exists('tagRouteCatalogParse')) {
    /** @return array{service:string,runtime:string,version:string,routes:list<array<string,mixed>>} */
    function tagRouteCatalogParse(string $yamlPath): array
    {
        $catalog = [
            'service' => 'tag',
            'runtime' => 'host-minimal',
            'version' => 'dev',
            'routes' => [],
        ];

        if (!is_file($yamlPath)) {
            return $catalog;
        }

        $parseValue = static function (string $raw): mixed {
            $value = trim($raw);
            if (
                (str_starts_with($value, "'") && str_ends_with($value, "'"))
                || (str_starts_with($value, '"') && str_ends_with($value, '"'))
            ) {
                $value = substr($value, 1, -1);
            }

            return match (strtolower($value)) {
                'true' => true,
                'false' => false,
                default => $value,
            };
        };

        $assign = static function (array &$target, string $key, string $raw) use ($parseValue): void {
            $target[$key] = $parseValue($raw);
        };

        $currentRoute = null;
        $insideRoutes = false;
        $lines = file($yamlPath, FILE_IGNORE_NEW_LINES) ?: [];
        foreach ($lines as $line) {
            $trimmed = trim($line);
            if ('' === $trimmed || str_starts_with($trimmed, '#')) {
                continue;
            }

            if ('routes:' === $trimmed) {
                $insideRoutes = true;
                continue;
            }

            if (!$insideRoutes) {
                if (1 === preg_match('/^([a-z_]+):\s*(.+)$/', $trimmed, $matches)) {
                    $assign($catalog, $matches[1], $matches[2]);
                }

                continue;
            }

            if (1 === preg_match('/^[^\s].*:\s*.+$/', $line)) {
                if (is_array($currentRoute)) {
                    $catalog['routes'][] = $currentRoute;
                    $currentRoute = null;
                }

                $insideRoutes = false;
                if (1 === preg_match('/^([a-z_]+):\s*(.+)$/', $trimmed, $matches)) {
                    $assign($catalog, $matches[1], $matches[2]);
                }
                continue;
            }

            if (1 === preg_match('/^\s{2}-\s([a-z_]+):\s*(.+)$/', $line, $matches)) {
                if (is_array($currentRoute)) {
                    $catalog['routes'][] = $currentRoute;
                }

                $currentRoute = [];
                $assign($currentRoute, $matches[1], $matches[2]);
                continue;
            }

            if (!is_array($currentRoute)) {
                continue;
            }

            if (1 === preg_match('/^\s{4}([a-z_]+):\s*(.+)$/', $line, $matches)) {
                $assign($currentRoute, $matches[1], $matches[2]);
            }
        }

        if (is_array($currentRoute)) {
            $catalog['routes'][] = $currentRoute;
        }

        return $catalog;
    }
}

return tagRouteCatalogParse(dirname(__DIR__) . '/tag.yaml');
