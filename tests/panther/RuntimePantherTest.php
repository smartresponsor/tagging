<?php

declare(strict_types=1);

namespace Tests\Panther;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Panther\Exception\RuntimeException;
use Symfony\Component\Panther\PantherTestCaseTrait;

final class RuntimePantherTest extends TestCase
{
    use PantherTestCaseTrait;

    public function testLiveRuntimeStatusEndpointResponds(): void
    {
        if (!$this->isChromeDriverAvailable()) {
            self::markTestSkipped('chromedriver binary not available in the local environment.');
        }

        $baseUri = (string) ($_SERVER['PANTHER_EXTERNAL_BASE_URI'] ?? $_SERVER['BASE_URL'] ?? 'http://127.0.0.1:8080');

        try {
            $client = self::createPantherClient([
                'browser' => 'chrome',
                'browser_arguments' => ['--headless=new', '--disable-dev-shm-usage', '--no-sandbox'],
                'external_base_uri' => $baseUri,
            ]);
        } catch (RuntimeException $exception) {
            if (str_contains($exception->getMessage(), 'chromedriver')) {
                self::markTestSkipped('chromedriver binary not available in the local environment.');
            }

            throw $exception;
        }

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

    private function isChromeDriverAvailable(): bool
    {
        $candidates = [];
        $envCandidate = getenv('PANTHER_CHROMEDRIVER_BINARY');
        if (is_string($envCandidate) && $envCandidate !== '') {
            $candidates[] = $envCandidate;
        }

        $binaryNames = DIRECTORY_SEPARATOR === '\\'
            ? ['chromedriver.exe', 'chromedriver']
            : ['chromedriver'];

        foreach ($binaryNames as $binaryName) {
            if ($this->resolveBinaryOnPath($binaryName) !== null) {
                return true;
            }
        }

        foreach ($candidates as $candidate) {
            if (is_file($candidate) && is_executable($candidate)) {
                return true;
            }
        }

        return false;
    }

    private function resolveBinaryOnPath(string $binaryName): ?string
    {
        $path = getenv('PATH');
        if (!is_string($path) || $path === '') {
            return null;
        }

        foreach (explode(PATH_SEPARATOR, $path) as $directory) {
            if ($directory === '') {
                continue;
            }

            $candidate = rtrim($directory, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $binaryName;
            if (is_file($candidate) && is_executable($candidate)) {
                return $candidate;
            }
        }

        return null;
    }
}
