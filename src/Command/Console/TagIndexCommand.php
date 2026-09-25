<?php

declare(strict_types=1);

namespace App\Tagging\Command\Console;

use App\Tagging\Service\Core\TagEntityQueryServiceInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'tag:index', description: 'List tags for a tenant.')]
final class TagIndexCommand extends TagAbstractCommand
{
    public function __construct(private readonly TagEntityQueryServiceInterface $query)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('tenant', null, InputOption::VALUE_REQUIRED, 'Tenant identifier.')
            ->addOption('limit', null, InputOption::VALUE_REQUIRED, 'Maximum rows.', '100')
            ->addOption('offset', null, InputOption::VALUE_REQUIRED, 'Row offset.', '0')
            ->addOption('json', null, InputOption::VALUE_NONE, 'Render machine-readable JSON.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $tenant = trim((string) $input->getOption('tenant'));
            if ('' === $tenant) {
                throw new \InvalidArgumentException('--tenant is required.');
            }

            $items = $this->query->index(
                $tenant,
                max(1, min(500, (int) $input->getOption('limit'))),
                max(0, (int) $input->getOption('offset')),
            );

            if ($input->getOption('json')) {
                $this->writeJson($io, ['ok' => true, 'items' => $items]);
            } else {
                $io->table(['id', 'slug', 'name', 'locale', 'weight'], array_map(
                    static fn(array $item): array => [
                        $item['id'] ?? '',
                        $item['slug'] ?? '',
                        $item['nameEntity'] ?? $item['name'] ?? '',
                        $item['locale'] ?? '',
                        $item['weight'] ?? 0,
                    ],
                    $items,
                ));
            }

            return self::SUCCESS;
        } catch (\Throwable $error) {
            return $this->fail($io, $error);
        }
    }
}
