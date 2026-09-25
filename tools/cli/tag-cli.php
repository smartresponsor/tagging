<?php

declare(strict_types=1);

$root = require __DIR__ . '/../tag-bootstrap.php';
$runtime = require $root . '/config/tag_runtime.php';

if (!is_array($runtime)) {
    fwrite(STDERR, json_encode(['ok' => false, 'code' => 'runtime_config_invalid'], JSON_UNESCAPED_SLASHES) . PHP_EOL);

    exit(1);
}

$argv = $_SERVER['argv'] ?? [];
array_shift($argv);

$command = $argv[0] ?? 'help';
$arguments = array_slice($argv, 1);

$pretty = false;
$filter = null;
foreach ($arguments as $argument) {
    if ('--pretty' === $argument) {
        $pretty = true;
        continue;
    }

    if (!str_starts_with($argument, '--') && null === $filter) {
        $filter = $argument;
    }
}

$commands = [
    'help' => ['description' => 'Show the local Tagging CLI command catalog.'],
    'list' => ['description' => 'Alias of help; optional filter argument is accepted.'],
    'status' => ['description' => 'Show dual-runtime status metadata.'],
    'surface' => ['description' => 'Show the current public surface contract.'],
    'create' => ['description' => 'Create a tag from a JSON payload.', 'implemented' => false],
    'get' => ['description' => 'Read one tag by id or slug.', 'implemented' => false],
    'patch' => ['description' => 'Patch a tag from a JSON payload.', 'implemented' => false],
    'delete' => ['description' => 'Delete a tag.', 'implemented' => false],
    'assign' => ['description' => 'Assign a tag to an entity.', 'implemented' => false],
    'unassign' => ['description' => 'Unassign a tag from an entity.', 'implemented' => false],
    'assignments' => ['description' => 'Inspect assignment routes and capabilities.', 'implemented' => false],
    'search' => ['description' => 'Search tags.', 'implemented' => false],
    'suggest' => ['description' => 'Suggest tags.', 'implemented' => false],
];

$encode = static function (mixed $payload) use ($pretty): string {
    $flags = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE;
    if ($pretty) {
        $flags |= JSON_PRETTY_PRINT;
    }

    return (string) json_encode($payload, $flags);
};

$emitStdout = static function (mixed $payload) use ($encode): void {
    fwrite(STDOUT, $encode($payload) . PHP_EOL);
};

$emitStderr = static function (mixed $payload) use ($encode): void {
    fwrite(STDERR, $encode($payload) . PHP_EOL);
};

$filteredCommands = $commands;
if (null !== $filter && '' !== $filter && !in_array($command, ['status', 'surface'], true)) {
    $filteredCommands = array_filter(
        $commands,
        static function (array $definition, string $name) use ($filter): bool {
            if (str_contains($name, $filter)) {
                return true;
            }

            if ('tag' === strtolower($filter)) {
                return true;
            }

            return str_contains(strtolower((string) ($definition['description'] ?? '')), strtolower($filter));
        },
        ARRAY_FILTER_USE_BOTH,
    );
}

switch ($command) {
    case 'help':
    case 'list':
        $emitStdout([
            'ok' => true,
            'service' => $runtime['service'] ?? 'tag',
            'runtime' => $runtime['runtime'] ?? 'dual-mode',
            'version' => $runtime['version'] ?? 'dev',
            'commands' => $filteredCommands,
        ]);
        exit(0);

    case 'status':
        $emitStdout([
            'ok' => true,
            'service' => $runtime['service'] ?? 'tag',
            'runtime' => $runtime['runtime'] ?? 'dual-mode',
            'version' => $runtime['version'] ?? 'dev',
            'routes' => $runtime['route'] ?? [],
        ]);
        exit(0);

    case 'surface':
        $emitStdout([
            'ok' => true,
            'service' => $runtime['service'] ?? 'tag',
            'runtime' => $runtime['runtime'] ?? 'dual-mode',
            'version' => $runtime['version'] ?? 'dev',
            'public_surface' => $runtime['public_surface'] ?? [],
            'route' => $runtime['route'] ?? [],
            'doc' => $runtime['doc'] ?? [],
            'example' => $runtime['example'] ?? [],
        ]);
        exit(0);

    default:
        $emitStderr([
            'ok' => false,
            'code' => 'invalid_cli_arguments',
            'message' => sprintf('Unknown command "%s". Use "help" to list supported commands.', $command),
        ]);
        exit(2);
}
