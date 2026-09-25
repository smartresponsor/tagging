<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);

$checks = [];
$failures = [];
$warnings = [];

$record = static function (string $id, string $status, string $message) use (&$checks, &$failures, &$warnings): void {
    $checks[] = [
        'id' => $id,
        'status' => $status,
        'message' => $message,
    ];

    if ('FAIL' === $status) {
        $failures[] = $id . ': ' . $message;
    }

    if ('WARN' === $status) {
        $warnings[] = $id . ': ' . $message;
    }
};

$exists = static function (string $relative) use ($root): bool {
    return is_file($root . '/' . $relative) || is_dir($root . '/' . $relative);
};

$read = static function (string $relative) use ($root): string {
    $path = $root . '/' . $relative;

    return is_file($path) ? (string) file_get_contents($path) : '';
};

// 1. Root entity truth.
$entity = 'src/Entity/Tag/TagEntity.php';
$record(
    'entity.root',
    $exists($entity) ? 'PASS' : 'FAIL',
    $exists($entity)
        ? 'Canonical root entity exists.'
        : 'Canonical root entity is missing: ' . $entity,
);

// 2. Zero-controller truth.
$controllerFiles = glob($root . '/src/Http/Api/Tag/**/*Controller.php') ?: [];
$controllerFiles = array_merge(
    $controllerFiles,
    glob($root . '/src/Http/Api/Tag/*Controller.php') ?: [],
);
$controllerFiles = array_values(array_unique(array_filter($controllerFiles, 'is_file')));

$record(
    'http.zero_controller',
    [] === $controllerFiles ? 'PASS' : 'FAIL',
    [] === $controllerFiles
        ? 'No active Tag controllers detected.'
        : sprintf('%d active controller file(s) remain.', count($controllerFiles)),
);

// 3. No legacy HttpService.
$legacyHttpServices = glob($root . '/src/Service/Http/Tag/*HttpService.php') ?: [];
$record(
    'http.no_legacy_http_service',
    [] === $legacyHttpServices ? 'PASS' : 'FAIL',
    [] === $legacyHttpServices
        ? 'No legacy *HttpService entrypoints detected.'
        : sprintf('%d legacy *HttpService file(s) remain.', count($legacyHttpServices)),
);

// 4. Canonical HTTP service surface.
$requiredHttpServices = [
    'TagIndexService',
    'TagShowService',
    'TagCreateService',
    'TagUpdateService',
    'TagDeleteService',
    'TagAssignmentIndexService',
    'TagAssignmentAssignService',
    'TagAssignmentUnassignService',
    'TagSearchService',
    'TagSuggestService',
    'TagRelationIndexService',
    'TagRelationCreateService',
    'TagSynonymIndexService',
    'TagSynonymCreateService',
    'TagProposalCreateService',
    'TagProposalApproveService',
    'TagClassificationIndexService',
    'TagStatusService',
    'TagMetricsService',
    'TagWebhookIndexService',
    'TagWebhookCreateService',
];

$missingHttpServices = [];
foreach ($requiredHttpServices as $service) {
    if (!$exists('src/Service/Http/Tag/' . $service . '.php')) {
        $missingHttpServices[] = $service;
    }
}

$record(
    'http.surface_complete',
    [] === $missingHttpServices ? 'PASS' : 'FAIL',
    [] === $missingHttpServices
        ? 'Required canonical HTTP service surface is complete.'
        : 'Missing HTTP services: ' . implode(', ', $missingHttpServices),
);

// 5. Route source of truth.
$routeSource = 'config/platform/routes/crud/tag.yaml';
$routeContent = $read($routeSource);

$record(
    'route.source_of_truth',
    '' !== $routeContent ? 'PASS' : 'FAIL',
    '' !== $routeContent
        ? 'Canonical Cruding registry exists.'
        : 'Canonical Cruding registry is missing.',
);

$legacyRouteSources = [
    'config/platform/routes/crud/tag-adjacent.yaml',
    'config/routes/tagging_native.yaml',
    'tag.yaml',
    'config/tag_route_catalog.php',
    'config/tag_public_route_paths.php',
];

$presentLegacyRouteSources = array_values(array_filter(
    $legacyRouteSources,
    static fn(string $path): bool => $exists($path),
));

$record(
    'route.no_parallel_sources',
    [] === $presentLegacyRouteSources ? 'PASS' : 'FAIL',
    [] === $presentLegacyRouteSources
        ? 'No parallel legacy route sources detected.'
        : 'Parallel route sources remain: ' . implode(', ', $presentLegacyRouteSources),
);

$routeKeys = [];
if ('' !== $routeContent) {
    preg_match_all('/^([a-z0-9_.]+):/m', $routeContent, $matches);
    $routeKeys = $matches[1] ?? [];
}

$duplicateRouteKeys = array_keys(array_filter(
    array_count_values($routeKeys),
    static fn(int $count): bool => $count > 1,
));

$record(
    'route.unique_keys',
    [] === $duplicateRouteKeys ? 'PASS' : 'FAIL',
    [] === $duplicateRouteKeys
        ? 'Route keys are unique.'
        : 'Duplicate route keys: ' . implode(', ', $duplicateRouteKeys),
);

// 6. Forms.
$formRoot = $root . '/src/Form/Tag';
$formFiles = is_dir($formRoot) ? (glob($formRoot . '/*Type.php') ?: []) : [];

preg_match_all(
    '/type:\s+App\\\\Tagging\\\\Form\\\\Tag\\\\([A-Za-z0-9_]+)/',
    $routeContent,
    $formMatches,
);
$routeFormTypes = array_values(array_unique($formMatches[1] ?? []));

$missingFormTypes = [];
foreach ($routeFormTypes as $type) {
    if (!is_file($formRoot . '/' . $type . '.php')) {
        $missingFormTypes[] = $type;
    }
}

$record(
    'forms.route_type_resolution',
    [] === $missingFormTypes ? 'PASS' : 'FAIL',
    [] === $missingFormTypes
        ? sprintf('%d route-referenced Symfony form types resolve.', count($routeFormTypes))
        : 'Missing form types: ' . implode(', ', $missingFormTypes),
);

$record(
    'forms.coverage_present',
    count($formFiles) >= 15 ? 'PASS' : 'WARN',
    sprintf('%d Tag form type file(s) detected.', count($formFiles)),
);

// 7. CLI commands.
$commandRoot = $root . '/src/Command/Tag';
$commandFiles = is_dir($commandRoot) ? (glob($commandRoot . '/*Command.php') ?: []) : [];

$requiredCommands = [
    'tag:index',
    'tag:show',
    'tag:create',
    'tag:update',
    'tag:delete',
    'tag:lifecycle',
    'tag:assignment',
    'tag:discover',
    'tag:relation:create',
    'tag:synonym:create',
    'tag:proposal',
    'tag:webhook',
    'tag:status',
];

$commandNames = [];
foreach ($commandFiles as $file) {
    $content = (string) file_get_contents($file);
    if (preg_match("/#\\[AsCommand\\(name:\\s*'([^']+)'/", $content, $match)) {
        $commandNames[] = $match[1];
    }
}

$missingCommands = array_values(array_diff($requiredCommands, $commandNames));

$record(
    'cli.coverage',
    [] === $missingCommands ? 'PASS' : 'FAIL',
    [] === $missingCommands
        ? sprintf('%d canonical CLI commands detected.', count($commandNames))
        : 'Missing CLI commands: ' . implode(', ', $missingCommands),
);

// 8. CLI must not depend on HTTP.
$cliHttpDependencies = [];
foreach ($commandFiles as $file) {
    $content = (string) file_get_contents($file);
    if (str_contains($content, 'App\\Tagging\\Service\\Http\\')) {
        $cliHttpDependencies[] = basename($file);
    }
}

$record(
    'cli.no_http_dependency',
    [] === $cliHttpDependencies ? 'PASS' : 'FAIL',
    [] === $cliHttpDependencies
        ? 'CLI commands do not depend on HTTP entrypoints.'
        : 'CLI→HTTP dependency found in: ' . implode(', ', $cliHttpDependencies),
);

// 9. Composer truth.
$composerPath = $root . '/composer.json';
$composer = is_file($composerPath)
    ? json_decode((string) file_get_contents($composerPath), true)
    : null;

if (!is_array($composer)) {
    $record('composer.valid_json', 'FAIL', 'composer.json is missing or invalid JSON.');
} else {
    $record('composer.valid_json', 'PASS', 'composer.json parses successfully.');

    $requiredPackages = [
        'symfony/console',
        'symfony/form',
        'symfony/validator',
        'symfony/options-resolver',
    ];

    $missingPackages = [];
    foreach ($requiredPackages as $package) {
        if (!isset($composer['require'][$package])) {
            $missingPackages[] = $package;
        }
    }

    $record(
        'composer.symfony_surface',
        [] === $missingPackages ? 'PASS' : 'FAIL',
        [] === $missingPackages
            ? 'Required Symfony Console/Form packages are declared.'
            : 'Missing packages: ' . implode(', ', $missingPackages),
    );

    $psr4 = $composer['autoload']['psr-4']['App\\Tagging\\'] ?? null;
    $record(
        'composer.psr4',
        null !== $psr4 ? 'PASS' : 'WARN',
        null !== $psr4
            ? 'App\\Tagging\\ PSR-4 mapping is declared.'
            : 'App\\Tagging\\ PSR-4 mapping was not found in this component composer.json.',
    );
}

// 10. Duplicate class declaration scan.
$classMap = [];
$duplicates = [];

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator(
        $root . '/src',
        FilesystemIterator::SKIP_DOTS,
    ),
);

foreach ($iterator as $file) {
    if (!$file->isFile() || 'php' !== strtolower($file->getExtension())) {
        continue;
    }

    $content = (string) file_get_contents($file->getPathname());

    if (!preg_match('/namespace\s+([^;]+);/', $content, $namespaceMatch)) {
        continue;
    }

    if (!preg_match('/\b(?:final\s+|abstract\s+|readonly\s+)*class\s+([A-Za-z0-9_]+)/', $content, $classMatch)
        && !preg_match('/\binterface\s+([A-Za-z0-9_]+)/', $content, $classMatch)
        && !preg_match('/\benum\s+([A-Za-z0-9_]+)/', $content, $classMatch)
    ) {
        continue;
    }

    $fqcn = trim($namespaceMatch[1]) . '\\' . $classMatch[1];

    if (isset($classMap[$fqcn])) {
        $duplicates[$fqcn] = [$classMap[$fqcn], $file->getPathname()];
    } else {
        $classMap[$fqcn] = $file->getPathname();
    }
}

$record(
    'php.duplicate_classes',
    [] === $duplicates ? 'PASS' : 'FAIL',
    [] === $duplicates
        ? 'No duplicate class/interface/enum declarations detected in src/.'
        : sprintf('%d duplicate declaration(s) detected.', count($duplicates)),
);

// 11. Doctrine relation boundary.
$entityFiles = glob($root . '/src/Entity/Tag/*.php') ?: [];
$externalDoctrineRelations = [];

foreach ($entityFiles as $file) {
    $content = (string) file_get_contents($file);

    if (preg_match_all(
        '/targetEntity:\s*([A-Za-z0-9_\\\\]+)::class/',
        $content,
        $matches,
    )) {
        foreach ($matches[1] as $target) {
            if (!str_starts_with($target, 'Tag')
                && !str_contains($target, '\\Tag\\')
                && !str_contains($target, 'App\\Tagging\\Entity\\Tag\\')
            ) {
                $externalDoctrineRelations[] = basename($file) . ' -> ' . $target;
            }
        }
    }
}

$record(
    'doctrine.boundary',
    [] === $externalDoctrineRelations ? 'PASS' : 'WARN',
    [] === $externalDoctrineRelations
        ? 'No obvious Doctrine relation from Tagging entities to external component entities.'
        : 'Review external Doctrine targets: ' . implode('; ', $externalDoctrineRelations),
);

// 12. Legacy TagLink truth.
$tagLinkResidue = [];
foreach ([
    'src/Entity/Tag/TagLink.php',
    'src/Entity/Tag/TagLinkEntity.php',
    'src/Repository/Tag/TagLinkRepository.php',
] as $path) {
    if ($exists($path)) {
        $tagLinkResidue[] = $path;
    }
}

$record(
    'legacy.taglink',
    [] === $tagLinkResidue ? 'PASS' : 'FAIL',
    [] === $tagLinkResidue
        ? 'No canonical TagLink class/repository residue detected.'
        : 'TagLink residue remains: ' . implode(', ', $tagLinkResidue),
);

// 13. System field contract.
$tagEntityContent = $read($entity);
$systemFieldSignals = [
    'createdAt' => ['createdAt', 'ObjectAuditEmbeddableTrait'],
    'updatedAt' => ['updatedAt', 'ObjectAuditEmbeddableTrait'],
    'deletedAt' => ['deletedAt', 'ObjectSoftDeleteEmbeddableTrait', 'ObjectSoftDeletableInterface'],
];

$missingSignals = [];
foreach ($systemFieldSignals as $signal => $acceptedEvidence) {
    $matched = false;
    foreach ($acceptedEvidence as $evidence) {
        if (str_contains($tagEntityContent, $evidence)) {
            $matched = true;
            break;
        }
    }

    if (!$matched) {
        $missingSignals[] = $signal;
    }
}

$record(
    'objecting.system_fields',
    [] === $missingSignals ? 'PASS' : 'WARN',
    [] === $missingSignals
        ? 'TagEntity exposes expected system-field signals.'
        : 'Review missing system-field signals: ' . implode(', ', $missingSignals),
);

// 14. Service config registrations.
$servicesContent = '';
foreach ([
    'config/component/services.yaml',
    'config/services.yaml',
    'config/services/tag_http.yaml',
    'config/services/tag_services.yaml',
] as $serviceConfig) {
    $servicesContent .= PHP_EOL . $read($serviceConfig);
}

$serviceRegistrations = [
    'App\\Tagging\\Service\\Http\\Tag\\',
    'App\\Tagging\\Command\\Tag\\',
];

$missingRegistrations = array_values(array_filter(
    $serviceRegistrations,
    static fn(string $registration): bool => !str_contains($servicesContent, $registration),
));

$record(
    'container.resource_registration',
    [] === $missingRegistrations ? 'PASS' : 'FAIL',
    [] === $missingRegistrations
        ? 'HTTP and CLI namespaces are registered in services.yaml.'
        : 'Missing service resources: ' . implode(', ', $missingRegistrations),
);

// 15. Documentation/release truth.
$record(
    'release.no_stale_manifest',
    !$exists('MANIFEST.json') ? 'PASS' : 'WARN',
    !$exists('MANIFEST.json')
        ? 'No stale root MANIFEST.json detected.'
        : 'Root MANIFEST.json exists and must be regenerated for the RC.',
);

$summary = [
    'generatedAt' => (new DateTimeImmutable())->format(DATE_ATOM),
    'component' => 'Tagging',
    'result' => [] === $failures ? 'PASS' : 'FAIL',
    'passCount' => count(array_filter($checks, static fn(array $check): bool => 'PASS' === $check['status'])),
    'warningCount' => count($warnings),
    'failureCount' => count($failures),
    'checks' => $checks,
];

$outputDir = $root . '/var/rc';
if (!is_dir($outputDir) && !mkdir($outputDir, 0777, true) && !is_dir($outputDir)) {
    fwrite(STDERR, "Unable to create var/rc output directory.\n");
    exit(2);
}

$jsonPath = $outputDir . '/tagging-rc-readiness.json';
$mdPath = $outputDir . '/tagging-rc-readiness.md';

file_put_contents(
    $jsonPath,
    json_encode($summary, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR) . PHP_EOL,
);

$lines = [
    '# Tagging RC Readiness',
    '',
    '- Generated: ' . $summary['generatedAt'],
    '- Result: **' . $summary['result'] . '**',
    '- Pass: ' . $summary['passCount'],
    '- Warnings: ' . $summary['warningCount'],
    '- Failures: ' . $summary['failureCount'],
    '',
    '| Gate | Status | Evidence |',
    '|---|---:|---|',
];

foreach ($checks as $check) {
    $lines[] = sprintf(
        '| `%s` | **%s** | %s |',
        $check['id'],
        $check['status'],
        str_replace('|', '\|', $check['message']),
    );
}

$lines[] = '';
$lines[] = [] === $failures
    ? '## Decision'
    : '## Blocking failures';

if ([] === $failures) {
    $lines[] = '';
    $lines[] = 'The static architecture gates pass. Runtime gates from the PowerShell orchestrator must also pass before publishing an RC tag.';
} else {
    foreach ($failures as $failure) {
        $lines[] = '- ' . $failure;
    }
}

if ([] !== $warnings) {
    $lines[] = '';
    $lines[] = '## Warnings';
    foreach ($warnings as $warning) {
        $lines[] = '- ' . $warning;
    }
}

file_put_contents($mdPath, implode(PHP_EOL, $lines) . PHP_EOL);

echo "Tagging RC static audit: {$summary['result']}\n";
echo "PASS={$summary['passCount']} WARN={$summary['warningCount']} FAIL={$summary['failureCount']}\n";
echo "JSON: {$jsonPath}\n";
echo "Markdown: {$mdPath}\n";

exit([] === $failures ? 0 : 1);
