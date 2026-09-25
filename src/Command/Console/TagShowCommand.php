<?php

declare(strict_types=1);

namespace App\Tagging\Command\Console;

use App\Tagging\Service\Core\TagEntityQueryServiceInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'tag:show', description: 'Show one tag by id or slug.')]
final class TagShowCommand extends TagAbstractCommand
{
    public function __construct(private readonly TagEntityQueryServiceInterface $query)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('reference', InputArgument::REQUIRED, 'Tag id or slug.')
            ->addOption('tenant', null, InputOption::VALUE_REQUIRED, 'Tenant identifier.')
            ->addOption('by', null, InputOption::VALUE_REQUIRED, 'Reference mode: id or slug.', 'id');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $tenant = trim((string) $input->getOption('tenant'));
            $reference = trim((string) $input->getArgument('reference'));
            $by = (string) $input->getOption('by');

            if ('' === $tenant) {
                throw new \InvalidArgumentException('--tenant is required.');
            }
            if (!in_array($by, ['id', 'slug'], true)) {
                throw new \InvalidArgumentException('--by must be id or slug.');
            }

            $item = 'slug' === $by
                ? $this->query->findBySlug($tenant, $reference)
                : $this->query->findById($tenant, $reference);

            if (null === $item) {
                $io->warning('Tag not found.');

                return self::INVALID;
            }

            $this->writeJson($io, ['ok' => true, 'item' => $item]);

            return self::SUCCESS;
        } catch (\Throwable $error) {
            return $this->fail($io, $error);
        }
    }
}
