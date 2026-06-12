<?php

declare(strict_types=1);

namespace App\Tagging\Command\Tag;

use App\Tagging\Service\Core\TagLifecycleServiceInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'tag:lifecycle', description: 'Archive or restore a tag.')]
final class TagLifecycleCommand extends AbstractTagCommand
{
    public function __construct(private readonly TagLifecycleServiceInterface $lifecycle)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('operation', InputArgument::REQUIRED, 'archive or restore')
            ->addArgument('reference', InputArgument::REQUIRED, 'Tag id or slug.')
            ->addOption('tenant', null, InputOption::VALUE_REQUIRED, 'Tenant identifier.')
            ->addOption('by', null, InputOption::VALUE_REQUIRED, 'Reference mode: id or slug.', 'id');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $operation = (string) $input->getArgument('operation');
            $reference = (string) $input->getArgument('reference');
            $tenant = trim((string) $input->getOption('tenant'));
            $by = (string) $input->getOption('by');

            if ('' === $tenant) {
                throw new \InvalidArgumentException('--tenant is required.');
            }
            if (!in_array($operation, ['archive', 'restore'], true)) {
                throw new \InvalidArgumentException('operation must be archive or restore.');
            }
            if (!in_array($by, ['id', 'slug'], true)) {
                throw new \InvalidArgumentException('--by must be id or slug.');
            }

            $id = 'id' === $by ? $reference : null;
            $slug = 'slug' === $by ? $reference : null;
            $item = 'archive' === $operation
                ? $this->lifecycle->archive($tenant, $id, $slug)
                : $this->lifecycle->restore($tenant, $id, $slug);

            $this->writeJson($io, ['ok' => true, 'item' => $item]);

            return self::SUCCESS;
        } catch (\Throwable $error) {
            return $this->fail($io, $error);
        }
    }
}
