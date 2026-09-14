<?php

declare(strict_types=1);

namespace App\Tagging\Command\Tag;

use App\Tagging\Entity\Tag\TagSynonymEntity;
use App\Tagging\Service\Core\TagRepositoryInterface;
use App\Tagging\Service\Core\TagUlidGenerator;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'tag:synonym:create', description: 'Create a synonym for a tag.')]
final class TagSynonymCreateCommand extends AbstractTagCommand
{
    public function __construct(private readonly TagRepositoryInterface $repository)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('tag-id', InputArgument::REQUIRED)
            ->addArgument('label', InputArgument::REQUIRED)
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

            $tag = $this->repository->getById($tenant, (string) $input->getArgument('tag-id'));
            if (null === $tag) {
                throw new \RuntimeException('Tag not found.');
            }

            $synonym = TagSynonymEntity::create(
                TagUlidGenerator::generate(),
                $tag,
                (string) $input->getArgument('label'),
            );
            $this->repository->saveSynonym($tenant, $synonym);

            $this->writeJson($io, [
                'ok' => true,
                'item' => [
                    'id' => $synonym->id(),
                    'tagId' => $synonym->tagId(),
                    'label' => $synonym->label(),
                ],
            ]);

            return self::SUCCESS;
        } catch (\Throwable $error) {
            return $this->fail($io, $error);
        }
    }
}
