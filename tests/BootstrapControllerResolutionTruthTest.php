<?php

declare(strict_types=1);

namespace Tests;

use App\Http\Api\Tag\AssignController;
use App\Http\Api\Tag\AssignmentReadController;
use App\Http\Api\Tag\SearchController;
use App\Http\Api\Tag\StatusController;
use App\Http\Api\Tag\SuggestController;
use App\Http\Api\Tag\SurfaceController;
use App\Http\Api\Tag\TagController;
use App\Http\Api\Tag\TagWebhookController;
use PHPUnit\Framework\TestCase;

final class BootstrapControllerResolutionTruthTest extends TestCase
{
    /** @return array<string, callable(): mixed> */
    private function bootstrapContainerOrSkip(): array
    {
        try {
            /** @var array<string, callable(): mixed> $container */
            $container = require __DIR__ . '/../host-minimal/bootstrap.php';

            return $container;
        } catch (\PDOException $exception) {
            self::markTestSkipped('Bootstrap controller resolution requires reachable DB: ' . $exception->getMessage());
        }
    }

    public function testBootstrapResolvesExpectedControllerInstances(): void
    {
        $container = $this->bootstrapContainerOrSkip();

        try {
            self::assertInstanceOf(StatusController::class, $container['statusController']());
            self::assertInstanceOf(SurfaceController::class, $container['surfaceController']());
            self::assertInstanceOf(TagController::class, $container['tagController']());
            self::assertInstanceOf(AssignController::class, $container['assignController']());
            self::assertInstanceOf(SearchController::class, $container['searchController']());
            self::assertInstanceOf(SuggestController::class, $container['suggestController']());
            self::assertInstanceOf(AssignmentReadController::class, $container['assignmentReadController']());
            self::assertInstanceOf(TagWebhookController::class, $container['webhookController']());
        } catch (\PDOException $exception) {
            self::markTestSkipped('Bootstrap controller resolution requires reachable DB: ' . $exception->getMessage());
        }
    }

    public function testResolvedControllerEntriesRemainStableAcrossRepeatedReads(): void
    {
        $container = $this->bootstrapContainerOrSkip();

        try {
            self::assertSame($container['statusController'](), $container['statusController']());
            self::assertSame($container['assignController'](), $container['assignController']());
            self::assertSame($container['searchController'](), $container['searchController']());
            self::assertSame($container['webhookController'](), $container['webhookController']());
        } catch (\PDOException $exception) {
            self::markTestSkipped('Bootstrap controller stability requires reachable DB: ' . $exception->getMessage());
        }
    }
}
