<?php

declare(strict_types=1);

require dirname(__DIR__, 2) . '/vendor/autoload.php';

/** @var array<string, mixed> $runtime */
$runtime = require dirname(__DIR__, 2) . '/config/tag_runtime.php';

$argv = $_SERVER['argv'] ?? [];
$command = $argv[1] ?? 'help';
$options = parseOptions(array_slice($argv, 2));
$pretty = isset($options['pretty']);

try {
    $payload = dispatch($command, $runtime);
    fwrite(STDOUT, encodeJson($payload, $pretty) . PHP_EOL);
    exit(0);
} catch (InvalidArgumentException $e) {
    fwrite(
        STDERR,
        encodeJson([
            'ok' => false,
            'code' => 'invalid_cli_arguments',
            'message' => $e->getMessage(),
        ], true) . PHP_EOL,
    );
    exit(2);
} catch (Throwable $e) {
    fwrite(
        STDERR,
        encodeJson([
            'ok' => false,
            'code' => 'cli_command_failed',
            'message' => $e->getMessage(),
        ], true) . PHP_EOL,
    );
    exit(1);
}

/** @return array<string, mixed> */
function dispatch(string $command, array $runtime): array
{
    return match ($command) {
        'help', '--help', '-h' => helpPayload(),
        'status' => (new App\Tagging\Http\Api\Tag\StatusController(runtime: $runtime))->status(),
        'surface' => (new App\Tagging\Http\Api\Tag\SurfaceController($runtime))->surface(),
        default => throw new InvalidArgumentException('Unknown command: ' . $command),
    };
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
            'assignments',
            'search',
            'suggest',
        ],
    ];
}

/** @param list<string> $argv */
function parseOptions(array $argv): array
{
    $options = [];
    foreach ($argv as $arg) {
        if (str_starts_with($arg, '--')) {
            $option = substr($arg, 2);
            if (false !== ($pos = strpos($option, '='))) {
                $options[substr($option, 0, $pos)] = substr($option, $pos + 1);
            } else {
                $options[$option] = true;
            }
        }
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

    return (string) json_encode($payload, $flags | JSON_THROW_ON_ERROR);
}
