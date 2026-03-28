<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

require dirname(__DIR__, 2) . '/host-minimal/autoload.php';

/** @var array<string, callable(): mixed> $container */
$container = require dirname(__DIR__, 2) . '/host-minimal/bootstrap.php';

$argv = $_SERVER['argv'] ?? [];
$command = $argv[1] ?? 'help';
$options = parseOptions(array_slice($argv, 2));
$pretty = isset($options['pretty']);

try {
    $payload = dispatch($command, $options, $container);
    fwrite(STDOUT, encodeJson($payload, $pretty) . PHP_EOL);
    exit(0);
} catch (InvalidArgumentException $e) {
    fwrite(STDERR, encodeJson(['ok' => false, 'code' => 'invalid_cli_arguments', 'message' => $e->getMessage()], true) . PHP_EOL);
    exit(2);
} catch (Throwable $e) {
    fwrite(STDERR, encodeJson(['ok' => false, 'code' => 'cli_command_failed', 'message' => $e->getMessage()], true) . PHP_EOL);
    exit(1);
}

/** @return array<string, mixed> */
function dispatch(string $command, array $options, array $container): array
{
    if (isHelpCommand($command)) {
        return helpPayload();
    }

    $commands = commandHandlers();
    $handler = $commands[$command] ?? null;
    if (!is_callable($handler)) {
        throw new InvalidArgumentException('Unknown command: ' . $command);
    }

    return $handler($options, $container);
}

/** @return array<string, mixed> */
function helpPayload(): array
{
    return [
        'ok' => true,
        'service' => 'tag',
        'cli' => 'tools/cli/tag.php',
        'commands' => [
            'help',
            'status',
            'surface',
            'create --tenant demo --json "{"name":"Alpha","slug":"alpha"}"',
            'get --tenant demo --id TAG_ID',
            'patch --tenant demo --id TAG_ID --json "{"name":"Beta"}"',
            'delete --tenant demo --id TAG_ID',
            'assign --tenant demo --tag TAG_ID --entity-type project --entity-id P1 [--idem key]',
            'unassign --tenant demo --tag TAG_ID --entity-type project --entity-id P1 [--idem key]',
            'assignments --tenant demo --entity-type project --entity-id P1 [--limit 50]',
            'search --tenant demo --q alpha [--page-size 20] [--page-token token]',
            'suggest --tenant demo --q al [--limit 10]',
        ],
    ];
}

/** @return array<string, mixed> */
function callStatus(array $container): array
{
    return $container['statusController']()->status();
}

/** @return array<string, mixed> */
function callSurface(array $container): array
{
    return $container['surfaceController']()->surface();
}

function isHelpCommand(string $command): bool
{
    return in_array($command, ['help', '--help', '-h'], true);
}

/** @return array<string, callable(array<string, string|bool>, array<string, callable():mixed>): array<string, mixed>>> */
function commandHandlers(): array
{
    return [
        'status' => static fn(array $options, array $container): array => callStatus($container),
        'surface' => static fn(array $options, array $container): array => callSurface($container),
        'create' => static fn(array $options, array $container): array => invokeController($container, 'tagController', 'create', buildWriteRequest($options)),
        'get' => static fn(array $options, array $container): array => invokeController($container, 'tagController', 'get', baseRequest($options), requireString($options, 'id')),
        'patch' => static fn(array $options, array $container): array => invokeController($container, 'tagController', 'patch', buildWriteRequest($options), requireString($options, 'id')),
        'delete' => static fn(array $options, array $container): array => invokeController($container, 'tagController', 'delete', baseRequest($options), requireString($options, 'id')),
        'assign' => static fn(array $options, array $container): array => invokeController($container, 'assignController', 'assign', buildAssignRequest($options), requireString($options, 'tag')),
        'unassign' => static fn(array $options, array $container): array => invokeController($container, 'assignController', 'unassign', buildAssignRequest($options), requireString($options, 'tag')),
        'assignments' => static fn(array $options, array $container): array => invokeController($container, 'assignmentReadController', 'listByEntity', buildAssignmentsRequest($options)),
        'search' => static fn(array $options, array $container): array => invokeController($container, 'searchController', 'get', buildSearchRequest($options)),
        'suggest' => static fn(array $options, array $container): array => invokeController($container, 'suggestController', 'get', buildSuggestRequest($options)),
    ];
}

/** @return array<string, mixed> */
function callTuple(object $controller, string $method, mixed ...$args): array
{
    /** @var array{0:int,1:array<string,string>,2:string} $tuple */
    $tuple = $controller->{$method}(...$args);
    $body = json_decode($tuple[2], true);
    if (!is_array($body)) {
        $body = ['raw' => $tuple[2]];
    }
    return [
        'ok' => $tuple[0] >= 200 && $tuple[0] < 300,
        'status' => $tuple[0],
        'headers' => $tuple[1],
        'body' => $body,
    ];
}

/** @return array<string, mixed> */
function baseRequest(array $options): array
{
    return ['headers' => tenantHeaders($options)];
}

/** @return array<string, mixed> */
function buildWriteRequest(array $options): array
{
    return baseRequest($options) + ['body' => readJsonBody($options)];
}

/** @return array<string, mixed> */
function buildAssignRequest(array $options): array
{
    return baseRequest($options) + [
        'body' => [
            'entityType' => requireString($options, 'entity-type'),
            'entityId' => requireString($options, 'entity-id'),
        ],
        'idemKey' => optionOrDefault($options, 'idem', ''),
    ];
}

/** @return array<string, mixed> */
function buildAssignmentsRequest(array $options): array
{
    return baseRequest($options) + [
        'query' => [
            'entityType' => requireString($options, 'entity-type'),
            'entityId' => requireString($options, 'entity-id'),
            'limit' => intOption($options, 'limit', 50),
        ],
    ];
}

/** @return array<string, mixed> */
function buildSearchRequest(array $options): array
{
    return baseRequest($options) + [
        'query' => [
            'q' => requireString($options, 'q'),
            'pageSize' => intOption($options, 'page-size', 20),
            'pageToken' => optionOrDefault($options, 'page-token', ''),
        ],
    ];
}

/** @return array<string, mixed> */
function buildSuggestRequest(array $options): array
{
    return baseRequest($options) + [
        'query' => [
            'q' => requireString($options, 'q'),
            'limit' => intOption($options, 'limit', 10),
        ],
    ];
}

/** @return array<string, mixed> */
function readJsonBody(array $options): array
{
    $json = optionOrDefault($options, 'json', '');
    if ($json === '') {
        return [];
    }

    $decoded = json_decode($json, true);
    if (!is_array($decoded)) {
        throw new InvalidArgumentException('Option --json must decode to an object/array.');
    }
    return $decoded;
}

function requireString(array $options, string $name): string
{
    $value = optionOrDefault($options, $name, '');
    if ($value === '') {
        throw new InvalidArgumentException('Missing required option --' . $name);
    }
    return $value;
}

function optionOrDefault(array $options, string $name, string $default): string
{
    $value = $options[$name] ?? $default;
    return is_string($value) ? $value : $default;
}

/** @return array<string, string> */
function tenantHeaders(array $options): array
{
    return ['x-tenant-id' => optionOrDefault($options, 'tenant', 'demo')];
}

function intOption(array $options, string $name, int $default): int
{
    return (int) optionOrDefault($options, $name, (string) $default);
}

/** @return array<string, mixed> */
function invokeController(array $container, string $serviceId, string $method, mixed ...$args): array
{
    return callTuple($container[$serviceId](), $method, ...$args);
}

/** @param list<string> $args @return array<string, string|bool> */
function parseOptions(array $args): array
{
    $options = [];
    $i = 0;
    while ($i < count($args)) {
        $token = $args[$i];
        if (!str_starts_with($token, '--')) {
            $i++;
            continue;
        }

        $token = substr($token, 2);
        if ($token === '') {
            $i++;
            continue;
        }

        if (str_contains($token, '=')) {
            [$key, $value] = explode('=', $token, 2);
            $options[$key] = $value;
            $i++;
            continue;
        }

        $next = $args[$i + 1] ?? null;
        if (is_string($next) && !str_starts_with($next, '--')) {
            $options[$token] = $next;
            $i += 2;
            continue;
        }

        $options[$token] = true;
        $i++;
    }

    return $options;
}

/** @param array<string, mixed> $payload */
function encodeJson(array $payload, bool $pretty): string
{
    $flags = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE;
    if ($pretty) {
        $flags |= JSON_PRETTY_PRINT;
    }
    return json_encode($payload, $flags) ?: '{}';
}
