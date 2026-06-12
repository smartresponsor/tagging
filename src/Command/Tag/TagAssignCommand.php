<?php

declare(strict_types=1);

namespace App\Tagging\Command\Tag;

use App\Tagging\Service\Core\TagAssignOperationInterface;
use App\Tagging\Service\Core\TagUnassignOperationInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'tag:assignment', description: 'Assign or unassign a tag from an object.')]
final class TagAssignCommand extends AbstractTagCommand
{
    public function __construct(
        private readonly TagAssignOperationInterface $assign,
        private readonly TagUnassignOperationInterface $unassign,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('operation', InputArgument::REQUIRED, 'assign or unassign')
            ->addArgument('tag-id', InputArgument::REQUIRED, 'Tag identifier.')
            ->addArgument('assigned-type', InputArgument::REQUIRED, 'Polymorphic object type.')
            ->addArgument('assigned-id', InputArgument::REQUIRED, 'Polymorphic object identifier.')
            ->addOption('tenant', null, InputOption::VALUE_REQUIRED, 'Tenant identifier.')
            ->addOption('idempotency-key', null, InputOption::VALUE_REQUIRED, 'Optional idempotency key.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $operation = (string) $input->getArgument('operation');
            $tenant = trim((string) $input->getOption('tenant'));

            if ('' === $tenant) {
                throw new \InvalidArgumentException('--tenant is required.');
            }
            if (!in_array($operation, ['assign', 'unassign'], true)) {
                throw new \InvalidArgumentException('operation must be assign or unassign.');
            }

            $arguments = [
                $tenant,
                (string) $input->getArgument('tag-id'),
                (string) $input->getArgument('assigned-type'),
                (string) $input->getArgument('assigned-id'),
                null !== $input->getOption('idempotency-key')
                    ? (string) $input->getOption('idempotency-key')
                    : null,
            ];

            $result = 'assign' === $operation
                ? $this->assign->assign(...$arguments)
                : $this->unassign->unassign(...$arguments);

            $this->writeJson($io, $result);

            return ($result['ok'] ?? false) ? self::SUCCESS : self::FAILURE;
        } catch (\Throwable $error) {
            return $this->fail($io, $error);
        }
    }
}
