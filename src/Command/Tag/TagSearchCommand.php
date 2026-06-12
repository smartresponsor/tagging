<?php

declare(strict_types=1);

namespace App\Tagging\Command\Tag;

use App\Tagging\Service\Core\TagSearchService;
use App\Tagging\Service\Core\TagSuggestService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'tag:discover', description: 'Search or suggest tags.')]
final class TagSearchCommand extends AbstractTagCommand
{
    public function __construct(
        private readonly TagSearchService $search,
        private readonly TagSuggestService $suggest,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('operation', InputArgument::REQUIRED, 'search or suggest')
            ->addArgument('query', InputArgument::OPTIONAL, 'Query text.', '')
            ->addOption('tenant', null, InputOption::VALUE_REQUIRED, 'Tenant identifier.')
            ->addOption('limit', null, InputOption::VALUE_REQUIRED, 'Result limit.', '20')
            ->addOption('page-token', null, InputOption::VALUE_REQUIRED, 'Search page token.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $operation = (string) $input->getArgument('operation');
            $tenant = trim((string) $input->getOption('tenant'));
            $query = (string) $input->getArgument('query');
            $limit = max(1, min(100, (int) $input->getOption('limit')));

            if ('' === $tenant) {
                throw new \InvalidArgumentException('--tenant is required.');
            }

            $result = match ($operation) {
                'search' => $this->search->search(
                    $tenant,
                    $query,
                    $limit,
                    $input->getOption('page-token'),
                ),
                'suggest' => $this->suggest->suggest($tenant, $query, min(50, $limit)),
                default => throw new \InvalidArgumentException('operation must be search or suggest.'),
            };

            $this->writeJson($io, ['ok' => true] + $result);

            return self::SUCCESS;
        } catch (\Throwable $error) {
            return $this->fail($io, $error);
        }
    }
}
