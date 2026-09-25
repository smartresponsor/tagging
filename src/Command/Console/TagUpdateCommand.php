<?php

declare(strict_types=1);

namespace App\Tagging\Command\Console;

use App\Tagging\Command\Input\TagPatchCommand as PatchTag;
use App\Tagging\HandlerInterface\Write\TagPatchHandlerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'tag:update', description: 'Patch a tag by id.')]
final class TagUpdateCommand extends TagAbstractCommand
{
    public function __construct(private readonly TagPatchHandlerInterface $useCase)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('id', InputArgument::REQUIRED, 'Tag identifier.')
            ->addOption('tenant', null, InputOption::VALUE_REQUIRED, 'Tenant identifier.')
            ->addOption('payload', null, InputOption::VALUE_REQUIRED, 'Patch JSON object.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $tenant = trim((string) $input->getOption('tenant'));
            if ('' === $tenant) {
                throw new \InvalidArgumentException('--tenant is required.');
            }

            $payload = $this->decodeObject((string) $input->getOption('payload'));
            $result = $this->useCase->execute(new PatchTag(
                $tenant,
                (string) $input->getArgument('id'),
                $payload,
            ));

            $this->writeJson($io, [
                'ok' => $result->ok,
                'status' => $result->status,
                'item' => $result->payload,
                'code' => $result->error?->value,
            ]);

            return $result->ok ? self::SUCCESS : self::FAILURE;
        } catch (\Throwable $error) {
            return $this->fail($io, $error);
        }
    }
}
