<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Http\Tag;

use App\Service\Tag\RedirectResolver;

/**
 *
 */

/**
 *
 */
final readonly class RedirectController
{
    /**
     * @param \App\Service\Tag\RedirectResolver $svc
     */
    public function __construct(private RedirectResolver $svc = new RedirectResolver())
    {
    }

    /**
     * @param array $params
     * @return array
     */
    public function resolve(array $params): array
    {
        $fromId = (string)($params['fromId'] ?? '');
        if ($fromId === '') return [400, ['Content-Type' => 'application/json'], json_encode(['code' => 'bad_request'])];
        $res = $this->svc->getTarget($fromId);
        return [200, ['Content-Type' => 'application/json'], json_encode($res)];
    }
}
