<?php

declare(strict_types=1);

namespace Tests\Panther;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Panther\PantherTestCaseTrait;

final class RuntimePantherTest extends TestCase
{
    use PantherTestCaseTrait;

    public function testLiveRuntimeStatusEndpointResponds(): void
    {
        $baseUri = (string) ($_SERVER['PANTHER_EXTERNAL_BASE_URI'] ?? $_SERVER['BASE_URL'] ?? 'http://127.0.0.1:8080');
        $client = self::createPantherClient([
            'browser' => 'chrome',
            'browser_arguments' => ['--headless=new', '--disable-dev-shm-usage', '--no-sandbox'],
            'external_base_uri' => $baseUri,
        ]);

        $client->request('GET', '/tag/_status');
        $raw = trim((string) $client->executeScript('return document.body ? document.body.innerText : "";'));
        if ($raw === '') {
            $raw = trim($client->getPageSource());
        }
        $payload = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);

        self::assertSame(200, $client->getInternalResponse()->getStatusCode());
        self::assertTrue($payload['ok']);
        self::assertTrue($payload['db']['ok']);
    }
}
