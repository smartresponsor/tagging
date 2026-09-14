<?php

declare(strict_types=1);

namespace App\Tagging\Service\Http\Tag;

use App\Tagging\Service\Core\Webhook\TagWebhookRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class TagWebhookCreateService extends AbstractTagService
{
    public function __construct(private TagWebhookRegistry $registry) {}

    public function __invoke(Request $request): Response
    {
        try {
            $payload = $this->payload($request);
            $url = trim((string) ($payload['url'] ?? ''));
            $secret = isset($payload['secret']) && '' !== trim((string) $payload['secret'])
                ? (string) $payload['secret']
                : null;

            if ('' === $url) {
                throw new \InvalidArgumentException('url_required');
            }

            $this->registry->add($url, $secret);

            return $this->json(['ok' => true, 'url' => $url], 201);
        } catch (\Throwable $error) {
            return $this->failure($error);
        }
    }
}
