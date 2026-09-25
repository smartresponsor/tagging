<?php

declare(strict_types=1);

namespace App\Tagging;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

/**
 * Boots Tagging as a standalone Symfony application for verification and local execution.
 */
final class Kernel extends BaseKernel
{
    use MicroKernelTrait;
}
