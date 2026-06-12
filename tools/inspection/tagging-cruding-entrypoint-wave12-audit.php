<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$failures = [];

$required = [
    'TagIndexService',
    'TagShowService',
    'TagNewService',
    'TagCreateService',
    'TagEditService',
    'TagUpdateService',
    'TagDeleteService',
    'TagBulkService',
    'TagImportService',
    'TagExportService',
    'TagArchiveService',
    'TagRestoreService',
    'TagDuplicateService',
];

foreach ($required as $class) {
    $file = $root . '/src/Service/Http/Tag/' . $class . '.php';
    if (!is_file($file)) {
        $failures[] = 'missing service: ' . $class;
    }
}

foreach (glob($root . '/src/Service/Http/Tag/*HttpService.php') ?: [] as $file) {
    $failures[] = 'legacy HttpService remains: ' . basename($file);
}

if (is_dir($root . '/src/Http/Api/Tag')) {
    $failures[] = 'legacy controller directory remains';
}

$routeMap = $root . '/config/platform/routes/crud/tag.yaml';
if (!is_file($routeMap)) {
    $failures[] = 'missing Cruding route map';
} else {
    $content = (string) file_get_contents($routeMap);
    foreach ($required as $class) {
        if (!str_contains($content, 'App\\Tagging\\Service\\Http\\Tag\\' . $class)) {
            $failures[] = 'route map does not reference: ' . $class;
        }
    }
}

if ([] !== $failures) {
    fwrite(STDERR, "Tagging Wave 12 audit FAILED\n");
    foreach ($failures as $failure) {
        fwrite(STDERR, " - {$failure}\n");
    }
    exit(1);
}

echo "Tagging Wave 12 audit PASSED\n";
echo "Zero-controller CRUD surface: App\\Tagging\\Service\\Http\\Tag\\Tag*Service\n";
