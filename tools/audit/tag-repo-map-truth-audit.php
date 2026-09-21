<?php

declare(strict_types=1);

$repoMapPath = __DIR__ . '/../../repo-map.md';
$repoMap = file_get_contents($repoMapPath);

$forbidden = [
    'host/',
    'src/Domain/',
    'src/Infra/',
    'src/Application/Tag/',
    'src/Service/Tag/',
    'src/ServiceInterface/',
    'src/Service/Audit/',
    'src/Service/Webhook/',
    'src/Service/Metric/',
    'src/Service/Security/Tag/',
    'fixtures/tag-demo.json',
];

$required = [
    'src/TaggingBundle.php',
    'contracts/http/tag-openapi.yaml',
    'fixtures/',
    'public/',
    'sdk/',
    'docs/public/',
    'docs/release/',
    'src/Application/Write/Tag/',
    'src/Cache/Store/Tag/',
    'src/Data/Model/Tag/',
    'src/Entity/Core/Tag/',
    'src/Event/Lifecycle/Tag/',
    'src/Http/Api/Tag/',
    'src/Infrastructure/Outbox/Tag/',
    'src/Infrastructure/Persistence/Tag/',
    'src/Infrastructure/ReadModel/Tag/',
    'src/Service/Core/Tag/',
];

$errors = [];
foreach ($forbidden as $needle) {
    if (preg_match('/^' . preg_quote($needle, '/') . '$/m', $repoMap) === 1) {
        $errors[] = sprintf('repo-map.md contains stale path: %s', $needle);
    }
}
foreach ($required as $needle) {
    if (!str_contains($repoMap, $needle)) {
        $errors[] = sprintf('repo-map.md misses canonical path: %s', $needle);
    }
}

if ($errors !== []) {
    fwrite(STDERR, implode(PHP_EOL, $errors) . PHP_EOL);
    exit(1);
}

echo 'repo-map truth audit passed
';
