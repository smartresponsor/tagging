<?php

declare(strict_types=1);

namespace App;

use App\DependencyInjection\TaggingExtension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Symfony bundle facade for the Tagging RC component.
 *
 * The component remains responsible for its own business surface.
 * The host application only enables this bundle and imports routes when needed.
 */
final class TaggingBundle extends Bundle
{
    public function getContainerExtension(): ?ExtensionInterface
    {
        return new TaggingExtension();
    }
}
