<?php

declare(strict_types=1);

namespace App\Tagging\Command\Tag;

use App\Tagging\Entity\Tag\TagRelationEntity;
use App\Tagging\Service\Core\TagRepositoryInterface;
use App\Tagging\Service\Core\TagUlidGenerator;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'tag:relation:create', description: 'Create a relation between two tags.')]
final class TagRelationCreateCommand extends AbstractTagCommand
{
    public function __construct(private readonly TagRepositoryInterface $repository)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('from-tag-id', InputArgument::REQUIRED)
            ->addArgument('to-tag-id', InputArgument::REQUIRED)
            ->addArgument('type', InputArgument::REQUIRED)
            ->addOption('tenant', null, InputOption::VALUE_REQUIRED, 'Tenant identifier.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $tenant = trim((string) $input->getOption('tenant'));
            if ('' === $tenant) {
                throw new \InvalidArgumentException('--tenant is required.');
            }

            $from = $this->repository->getById($tenant, (string) $input->getArgument('from-tag-id'));
            $to = $this->repository->getById($tenant, (string) $input->getArgument('to-tag-id'));

            if (null === $from || null === $to) {
                throw new \RuntimeException('One or both tags were not found.');
            }

            $relation = TagRelationEntity::create(
                TagUlidGenerator::generate(),
                $from,
                $to,
                (string) $input->getArgument('type'),
            );
            $this->repository->saveRelation($tenant, $relation);

            $this->writeJson($io, [
                'ok' => true,
                'item' => [
                    'id' => $relation->id(),
                    'fromTagId' => $relation->fromTagId(),
                    'toTagId' => $relation->toTagId(),
                    'type' => $relation->type(),
                ],
            ]);

            return self::SUCCESS;
        } catch (\Throwable $error) {
            return $this->fail($io, $error);
        }
    }
}
