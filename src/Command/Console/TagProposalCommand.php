<?php

declare(strict_types=1);

namespace App\Tagging\Command\Console;

use App\Tagging\Service\Core\TagModerationService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'tag:proposal', description: 'Create or approve a tag proposal.')]
final class TagProposalCommand extends TagAbstractCommand
{
    public function __construct(private readonly TagModerationService $moderation)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('operation', InputArgument::REQUIRED, 'create or approve')
            ->addArgument('reference', InputArgument::REQUIRED, 'Proposal type for create; proposal id for approve.')
            ->addOption('tenant', null, InputOption::VALUE_REQUIRED, 'Tenant identifier.')
            ->addOption('payload', null, InputOption::VALUE_REQUIRED, 'Proposal JSON object.', '{}')
            ->addOption('actor', null, InputOption::VALUE_REQUIRED, 'Approving actor id.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $operation = (string) $input->getArgument('operation');
            $reference = (string) $input->getArgument('reference');
            $tenant = trim((string) $input->getOption('tenant'));

            if ('' === $tenant) {
                throw new \InvalidArgumentException('--tenant is required.');
            }

            if ('create' === $operation) {
                $id = $this->moderation->propose(
                    $tenant,
                    $reference,
                    $this->decodeObject((string) $input->getOption('payload')),
                );
                $this->writeJson($io, ['ok' => true, 'id' => $id]);

                return self::SUCCESS;
            }

            if ('approve' === $operation) {
                $actor = trim((string) $input->getOption('actor'));
                if ('' === $actor) {
                    throw new \InvalidArgumentException('--actor is required for approve.');
                }

                $this->moderation->approve($tenant, $reference, $actor);
                $this->writeJson($io, ['ok' => true, 'id' => $reference]);

                return self::SUCCESS;
            }

            throw new \InvalidArgumentException('operation must be create or approve.');
        } catch (\Throwable $error) {
            return $this->fail($io, $error);
        }
    }
}
