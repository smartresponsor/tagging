<?php

declare(strict_types=1);

namespace App\Tagging\Service\Http\Tag;

use App\Tagging\Application\Write\Tag\Dto\TagError;
use App\Tagging\Application\Write\Tag\Dto\TagResult;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

abstract class AbstractTagService
{
    final protected function tenant(Request $request): string
    {
        $tenant = trim((string) (
            $request->attributes->get('tenant')
            ?? $request->headers->get('X-Tenant-Id', '')
        ));

        if ('' === $tenant) {
            throw new \InvalidArgumentException('invalid_tenant');
        }

        return $tenant;
    }

    /** @return array<string, mixed> */
    final protected function payload(Request $request): array
    {
        if (str_contains((string) $request->headers->get('Content-Type', ''), 'application/json')) {
            $decoded = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);

            return is_array($decoded) ? $decoded : [];
        }

        return $request->request->all();
    }

    final protected function json(array $payload, int $status = 200): JsonResponse
    {
        return new JsonResponse($payload, $status);
    }

    final protected function result(TagResult $result): JsonResponse
    {
        if ($result->ok) {
            return $this->json(
                [] === $result->payload
                    ? ['ok' => true]
                    : ['ok' => true, 'item' => $result->payload],
                $result->status,
            );
        }

        $error = $result->error ?? TagError::ValidationFailed;

        return $this->json(
            ['ok' => false, 'code' => $error->value],
            match ($error) {
                TagError::InvalidTenant,
                TagError::ValidationFailed => 400,
                TagError::NotFound => 404,
                TagError::Conflict => 409,
            },
        );
    }

    final protected function failure(\Throwable $error): JsonResponse
    {
        return match (true) {
            $error instanceof \InvalidArgumentException => $this->json(
                ['ok' => false, 'code' => $error->getMessage()],
                400,
            ),
            default => $this->json(
                ['ok' => false, 'code' => 'internal_error'],
                500,
            ),
        };
    }
}
