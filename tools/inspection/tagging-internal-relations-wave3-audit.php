<?php

declare(strict_types=1);

$root = dirname(__DIR__, 3);
$files = [
    $root . '/src/Entity/Tag/TagRelationEntity.php' => ['fromTag', 'toTag', 'JoinColumns', 'from_tag_id', 'to_tag_id'],
    $root . '/src/Entity/Tag/TagSynonymEntity.php' => ['tag()', 'JoinColumns', 'tag_id'],
    $root . '/src/Entity/Tag/TagRedirectEntity.php' => ['toTag()', 'JoinColumns', 'to_tag_id'],
];

$errors = [];

foreach ($files as $file => $needles) {
    if (!is_file($file)) {
        $errors[] = 'Missing file: ' . $file;
        continue;
    }

    $contents = (string) file_get_contents($file);

    if (str_contains($contents, 'name:')) {
        $errors[] = 'Doctrine typo remains in ' . $file . ': name:';
    }

    foreach ($needles as $needle) {
        if (!str_contains($contents, $needle)) {
            $errors[] = 'Missing expected marker in ' . $file . ': ' . $needle;
        }
    }
}

$assignmentFile = $root . '/src/Entity/Tag/TagAssignmentEntity.php';
if (is_file($assignmentFile)) {
    $contents = (string) file_get_contents($assignmentFile);
    if (str_contains($contents, 'assigned_type') && str_contains($contents, 'assigned_id') && str_contains($contents, 'ManyToOne')) {
        $errors[] = 'TagAssignmentEntity must remain polymorphic and must not contain ManyToOne.';
    }
}

$linkFile = $root . '/src/Entity/Tag/TagLinkEntity.php';
if (!is_file($linkFile)) {

}

if ($errors !== []) {
    echo "Tagging internal relations Wave 3 audit: FAIL\n";
    foreach ($errors as $error) {
        echo ' - ' . $error . "\n";
    }
    exit(1);
}

echo "Tagging internal relations Wave 3 audit: OK\n";
echo "Internal Tag->Tag / Tag->Synonym / Tag->Redirect relations are present.\n";
echo "External object assignment remains polymorphic.\n";
