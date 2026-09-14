<?php

declare(strict_types=1);

namespace App\Tagging\Repository\Data\Tag;

if (!class_exists(TagEntityRepository::class, false)) {
    class_alias(\App\Tagging\Repository\Core\Tag\TagRepository::class, 'App\\Tagging\\Repository\\Data\\Tag\\TagEntityRepository');
}
