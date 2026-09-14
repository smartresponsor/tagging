<?php

declare(strict_types=1);

namespace App\Tagging\Data\Model\Tag;

if (!class_exists(TagEntity::class, false)) {
    class_alias(\App\Tagging\Entity\Tag\TagEntity::class, 'App\\Tagging\\Data\\Model\\Tag\\TagEntity');
}
