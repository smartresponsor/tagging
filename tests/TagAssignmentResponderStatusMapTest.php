<?php

declare(strict_types=1);

namespace Tests;

use App\Http\Api\Tag\Responder\TagAssignmentResponder;
use PHPUnit\Framework\TestCase;

final class TagAssignmentResponderStatusMapTest extends TestCase
{
    public function testStatusForKnownCodesMatchesCurrentTransportContract(): void
    {
        $responder = new TagAssignmentResponder();

        self::assertSame(404, $responder->statusForCode('tag_not_found'));
        self::assertSame(409, $responder->statusForCode('idempotency_conflict'));
        self::assertSame(500, $responder->statusForCode('assign_failed'));
        self::assertSame(500, $responder->statusForCode('unassign_failed'));
        self::assertSame(400, $responder->statusForCode('invalid_tenant'));
        self::assertSame(400, $responder->statusForCode('validation_failed'));
    }

    public function testStatusFallsBackToServerErrorForUnknownOrMissingCodes(): void
    {
        $responder = new TagAssignmentResponder();

        self::assertSame(500, $responder->statusForCode(null));
        self::assertSame(500, $responder->statusForCode(''));
        self::assertSame(500, $responder->statusForCode('unknown_code'));
    }

    public function testFailurePayloadCarriesCodeAndSupplementalFields(): void
    {
        $responder = new TagAssignmentResponder();
        [$status, $headers, $body] = $responder->failure('validation_failed', 400, [
            'ok' => false,
            'entityType' => 'project',
            'entityId' => 'proj-1',
        ]);

        $payload = json_decode($body, true, 512, JSON_THROW_ON_ERROR);

        self::assertSame(400, $status);
        self::assertSame('application/json', $headers['Content-Type'] ?? null);
        self::assertSame('validation_failed', $payload['code']);
        self::assertFalse($payload['ok']);
        self::assertSame('project', $payload['entityType']);
        self::assertSame('proj-1', $payload['entityId']);
    }
}
