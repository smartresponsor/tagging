<?php

declare(strict_types=1);

namespace Tests;

use App\Http\Api\Tag\AssignmentReadController;
use App\Http\Api\Tag\AssignController;
use App\Http\Api\Tag\SearchController;
use App\Http\Api\Tag\StatusController;
use App\Http\Api\Tag\SuggestController;
use App\Http\Api\Tag\SurfaceController;
use App\Http\Api\Tag\TagController;
use App\Http\Api\Tag\TagWebhookController;
use PHPUnit\Framework\TestCase;

final class BootstrapControllerResolutionTruthTest extends TestCase
{
    public function testBootstrapResolvesExpectedControllerInstances(): void
    {
        $container = require dirname(__DIR__) . '/host-minimal/bootstrap.php';

        self::assertInstanceOf(StatusController::class, $container['statusController']());
        self::assertInstanceOf(SurfaceController::class, $container['surfaceController']());
        self::assertInstanceOf(TagController::class, $container['tagController']());
        self::assertInstanceOf(AssignController::class, $container['assignController']());
        self::assertInstanceOf(SearchController::class, $container['searchController']());
        self::assertInstanceOf(SuggestController::class, $container['suggestController']());
        self::assertInstanceOf(AssignmentReadController::class, $container['assignmentReadController']());
        self::assertInstanceOf(TagWebhookController::class, $container['webhookController']());
    }

    public function testResolvedControllerEntriesRemainStableAcrossRepeatedReads(): void
    {
        $container = require dirname(__DIR__) . '/host-minimal/bootstrap.php';

        self::assertSame($container['statusController'](), $container['statusController']());
        self::assertSame($container['assignController'](), $container['assignController']());
        self::assertSame($container['searchController'](), $container['searchController']());
        self::assertSame($container['webhookController'](), $container['webhookController']());
    }
}
