<?php

declare(strict_types=1);

$root = dirname(__DIR__, 3);
$tagging = $root . '/Tagging';
$objecting = $root . '/Objecting';

$failures = [];
$warnings = [];

$requiredFiles = [
    $tagging . '/src/Entity/Tag/TagEntity.php',
    $objecting . '/src/EntityTrait/Embeddable/ObjectIdentityEmbeddableTrait.php',
    $objecting . '/src/EntityTrait/Embeddable/ObjectAuditEmbeddableTrait.php',
    $objecting . '/src/EntityTrait/Embeddable/ObjectTitleEmbeddableTrait.php',
    $objecting . '/src/EntityInterface/ObjectIdentifiedInterface.php',
    $objecting . '/src/EntityInterface/ObjectAuditedInterface.php',
    $objecting . '/src/EntityInterface/ObjectTitledInterface.php',
];

foreach ($requiredFiles as $file) {
    if (!is_file($file)) {
        $failures[] = 'Missing required file: ' . str_replace($root . '/', '', $file);
    }
}

$entityFile = $tagging . '/src/Entity/Tag/TagEntity.php';
$entity = is_file($entityFile) ? file_get_contents($entityFile) : '';

foreach ([
    'ObjectIdentifiedInterface',
    'ObjectAuditedInterface',
    'ObjectTitledInterface',
    'ObjectIdentityEmbeddableTrait',
    'ObjectAuditEmbeddableTrait',
    'ObjectTitleEmbeddableTrait',
    'initializeObjectIdentity(null, $slug)',
    'initializeObjectAudit($createdAt)',
    'initializeObjectTitle($label)',
    'setFirstTitle($label)',
    'setObjectSlug($slug)',
    'touchModified($now)',
] as $needle) {
    if (!str_contains($entity, $needle)) {
        $failures[] = 'TagEntity does not contain required Objecting adoption marker: ' . $needle;
    }
}

$scanDirs = [
    $tagging . '/src/Entity',
    $objecting . '/src/Embeddable',
];

foreach ($scanDirs as $dir) {
    if (!is_dir($dir)) {
        $warnings[] = 'Scan directory missing: ' . str_replace($root . '/', '', $dir);
        continue;
    }

    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    foreach ($it as $file) {
        if (!$file->isFile() || $file->getExtension() !== 'php') {
            continue;
        }
        $path = $file->getPathname();
        $contents = file_get_contents($path);
        if (str_contains($contents, 'name:')) {
            $failures[] = 'Doctrine attribute still uses name: ' . str_replace($root . '/', '', $path);
        }
    }
}

if ($warnings !== []) {
    echo "Warnings:\n";
    foreach ($warnings as $warning) {
        echo '  - ' . $warning . "\n";
    }
    echo "\n";
}

if ($failures !== []) {
    echo "Tagging Objecting adoption Wave 2 audit: FAIL\n";
    foreach ($failures as $failure) {
        echo '  - ' . $failure . "\n";
    }
    exit(1);
}

echo "Tagging Objecting adoption Wave 2 audit: OK\n";
echo "  - TagEntity adopts Objecting identity/audit/title field packs.\n";
echo "  - Tagging entity and Objecting embeddable Doctrine attributes no longer use nameEntity.\n";
