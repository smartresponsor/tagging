<?php

declare(strict_types=1);

namespace App\Tagging\Command\Tag;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;

abstract class AbstractTagCommand extends Command
{
    /** @return array<string, mixed> */
    final protected function decodeObject(string $json, string $label = 'payload'): array
    {
        try {
            $value = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $error) {
            throw new \InvalidArgumentException(sprintf('Invalid %s JSON: %s', $label, $error->getMessage()), 0, $error);
        }

        if (!is_array($value)) {
            throw new \InvalidArgumentException(sprintf('%s must decode to a JSON object.', ucfirst($label)));
        }

        return $value;
    }

    final protected function writeJson(SymfonyStyle $io, mixed $value): void
    {
        $io->writeln((string) json_encode(
            $value,
            JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
        ));
    }

    final protected function fail(SymfonyStyle $io, \Throwable $error): int
    {
        $io->error($error->getMessage());

        return self::FAILURE;
    }
}
