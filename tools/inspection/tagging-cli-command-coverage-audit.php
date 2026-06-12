<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$failures = [];

$required = [
    'tag:index' => 'TagIndexCommand.php',
    'tag:show' => 'TagShowCommand.php',
    'tag:create' => 'TagCreateCommand.php',
    'tag:update' => 'TagUpdateCommand.php',
    'tag:delete' => 'TagDeleteCommand.php',
    'tag:lifecycle' => 'TagLifecycleCommand.php',
    'tag:assignment' => 'TagAssignCommand.php',
    'tag:discover' => 'TagSearchCommand.php',
    'tag:relation:create' => 'TagRelationCreateCommand.php',
    'tag:synonym:create' => 'TagSynonymCreateCommand.php',
    'tag:proposal' => 'TagProposalCommand.php',
    'tag:webhook' => 'TagWebhookCommand.php',
    'tag:status' => 'TagStatusCommand.php',
];

foreach ($required as $name => $file) {
    $path = $root . '/src/Command/Tag/' . $file;

    if (!is_file($path)) {
        $failures[] = 'missing command file: ' . $file;
        continue;
    }

    $content = (string) file_get_contents($path);
    if (!str_contains($content, "#[AsCommand(name: '{$name}'")) {
        $failures[] = 'command name mismatch: ' . $name;
    }

    if (str_contains($content, 'App\\Tagging\\Service\\Http\\')) {
        $failures[] = 'CLI command depends on HTTP service: ' . $file;
    }
}

$composer = json_decode((string) file_get_contents($root . '/composer.json'), true);
if (!isset($composer['require']['symfony/console'])) {
    $failures[] = 'composer requirement missing: symfony/console';
}

$services = (string) file_get_contents($root . '/config/component/services.yaml');
if (!str_contains($services, 'App\\Tagging\\Command\\Tag\\')) {
    $failures[] = 'command resource is not registered in config/component/services.yaml';
}

if ([] !== $failures) {
    fwrite(STDERR, "Tagging CLI command coverage audit FAILED\n");
    foreach ($failures as $failure) {
        fwrite(STDERR, " - {$failure}\n");
    }
    exit(1);
}

echo "Tagging CLI command coverage audit PASSED\n";
echo "Commands share application/core responsibilities and do not depend on HTTP services.\n";
