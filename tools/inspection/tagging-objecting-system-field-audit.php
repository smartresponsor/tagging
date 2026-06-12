<?php

declare(strict_types=1);

$root = dirname(__DIR__, 3);
$taggingEntityDir = $root . '/src/Entity';
$objectingDir = dirname($root) . '/Objecting/src';

$targets = [
    'Tagging entities' => $taggingEntityDir,
    'Objecting src' => $objectingDir,
];

$systemFieldPatterns = [
    'createdAt' => '/\$createdAt\b/',
    'updatedAt' => '/\$updatedAt\b/',
    'createdBy' => '/\$createdBy\b/',
    'updatedBy' => '/\$updatedBy\b/',
    'slug' => '/\$slug\b/',
    'label' => '/\$label\b/',
    'nameEntity-doctrine-arg' => '/nameEntity\s*:/',
];

$relationCandidates = [
    'TagRelationEntity.php' => ['fromTagId', 'toTagId'],
    'TagSynonymEntity.php' => ['tagId'],
    'TagRedirectEntity.php' => ['toTagId'],
    'TagAssignmentEntity.php' => ['tagId', 'assignedType', 'assignedId'],

];

function files(string $dir): array
{
    if (!is_dir($dir)) {
        return [];
    }

    $rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS));
    $out = [];
    foreach ($rii as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            $out[] = $file->getPathname();
        }
    }
    sort($out);

    return $out;
}

$exitCode = 0;

echo "Tagging/Objecting system field audit\n";
echo "Root: {$root}\n\n";

foreach ($targets as $label => $dir) {
    echo "== {$label}: {$dir}\n";
    if (!is_dir($dir)) {
        echo "MISSING\n\n";
        $exitCode = 1;
        continue;
    }

    $hits = [];
    foreach (files($dir) as $file) {
        $content = file_get_contents($file);
        if (!is_string($content)) {
            continue;
        }
        foreach ($systemFieldPatterns as $name => $pattern) {
            if (preg_match($pattern, $content)) {
                $hits[] = [str_replace(dirname($root) . '/', '', $file), $name];
            }
        }
    }

    if ($hits === []) {
        echo "No system-field or nameEntity hits detected.\n\n";
        continue;
    }

    foreach ($hits as [$file, $name]) {
        echo "- {$file}: {$name}\n";
        if ($name === 'nameEntity-doctrine-arg') {
            $exitCode = 2;
        }
    }
    echo "\n";
}

echo "== Tagging relation candidates\n";
foreach ($relationCandidates as $fileName => $fields) {
    $matches = glob($taggingEntityDir . '/**/' . $fileName, GLOB_BRACE) ?: [];
    if ($matches === []) {
        $matches = glob($taggingEntityDir . '/Tag/' . $fileName) ?: [];
    }

    foreach ($matches as $file) {
        $content = file_get_contents($file);
        if (!is_string($content)) {
            continue;
        }
        $present = [];
        foreach ($fields as $field) {
            if (str_contains($content, '$' . $field)) {
                $present[] = $field;
            }
        }
        echo '- ' . str_replace(dirname($root) . '/', '', $file) . ': ' . implode(', ', $present) . "\n";
    }
}

echo "\nResult: ";
if ($exitCode === 0) {
    echo "OK\n";
} elseif ($exitCode === 2) {
    echo "BLOCKED_BY_DOCTRINE_NAME_ENTITY\n";
    echo "Fix Doctrine attribute named argument nameEntity -> name before applying Objecting embeddable migrations.\n";
} else {
    echo "INCOMPLETE\n";
}

exit($exitCode);
