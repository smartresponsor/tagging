<?php

declare(strict_types=1);

namespace App\Tagging\Command\Tag;

use App\Tagging\Application\Write\Tag\Dto\TagDeleteCommand as DeleteTag;
use App\Tagging\Application\Write\Tag\UseCase\TagDeleteUseCaseInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'tag:delete', description: 'Delete a tag by id.')]
final class TagDeleteCommand extends AbstractTagCommand
{
    public function __construct(private readonly TagDeleteUseCaseInterface $useCase)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('id', InputArgument::REQUIRED, 'Tag identifier.')
            ->addOption('tenant', null, InputOption::VALUE_REQUIRED, 'Tenant identifier.')
            ->addOption('force', null, InputOption::VALUE_NONE, 'Confirm destructive execution.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            if (!$input->getOption('force')) {
                $io->warning('Use --force to confirm deletion.');

                return self::INVALID;
            }

            $tenant = trim((string) $input->getOption('tenant'));
            if ('' === $tenant) {
                throw new \InvalidArgumentException('--tenant is required.');
            }

            $result = $this->useCase->execute(new DeleteTag(
                $tenant,
                (string) $input->getArgument('id'),
            ));

            $this->writeJson($io, [
                'ok' => $result->ok,
                'status' => $result->status,
                'code' => $result->error?->value,
            ]);

            return $result->ok ? self::SUCCESS : self::FAILURE;
        } catch (\Throwable $error) {
            return $this->fail($io, $error);
        }
    }
}
