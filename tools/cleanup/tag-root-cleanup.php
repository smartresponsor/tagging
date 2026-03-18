<?php
    declare(strict_types=1);

    $root = dirname(__DIR__, 2);
    $targets = [
        'tag.yaml',
        'tag_assignment.yaml',
        'tag_quota.yaml',
        'ZZ_CHANGED_FILES.txt',
        'ZZ_REMOVE_ROOT_ITEMS.txt',
        'ZZ_WAVE.txt',
        'ZZ_NEXT.txt',
        'ZZ_REMOVED_FILES.txt',
        'ZZ_REMOVE_EMPTY_DIRS.txt',
        'ZZ_MOVE_MAP.txt',
        'tag_cons_patched',
        'tag_fix',
        'tmp',
    ];

    $removed = [];
    $missing = [];

    $remove = static function (string $path) use (&$remove): void {
        if (is_dir($path) && !is_link($path)) {
            $items = scandir($path) ?: [];
            foreach ($items as $item) {
                if ($item === '.' || $item === '..') {
                    continue;
                }
                $remove($path . DIRECTORY_SEPARATOR . $item);
            }
            @rmdir($path);
            return;
        }

        if (file_exists($path) || is_link($path)) {
            @unlink($path);
        }
    };

    foreach ($targets as $target) {
        $path = $root . DIRECTORY_SEPARATOR . $target;
        if (!file_exists($path) && !is_link($path)) {
            $missing[] = $target;
            continue;
        }

        $remove($path);
        $removed[] = $target;
    }

    echo "Root cleanup completed.
";
    if ($removed !== []) {
        echo "Removed:
";
        foreach ($removed as $item) {
            echo "- {$item}
";
        }
    }
    if ($missing !== []) {
        echo "Already absent:
";
        foreach ($missing as $item) {
            echo "- {$item}
";
        }
    }
