<?php

declare(strict_types=1);

namespace App\Tagging\Command\Tag;

use App\Tagging\Application\Write\Tag\Dto\TagCreateCommand as CreateTag;
use App\Tagging\Application\Write\Tag\UseCase\TagCreateUseCaseInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'tag:create', description: 'Create a tag.')]
final class TagCreateCommand extends AbstractTagCommand
{
    public function __construct(private readonly TagCreateUseCaseInterface $useCase)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('tenant', null, InputOption::VALUE_REQUIRED, 'Tenant identifier.')
            ->addOption('name', null, InputOption::VALUE_REQUIRED, 'Tag name.')
            ->addOption('slug', null, InputOption::VALUE_REQUIRED, 'Optional slug.')
            ->addOption('locale', null, InputOption::VALUE_REQUIRED, 'Locale.', 'en')
            ->addOption('weight', null, InputOption::VALUE_REQUIRED, 'Weight.', '0')
            ->addOption('payload', null, InputOption::VALUE_REQUIRED, 'Additional JSON object.', '{}');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $tenant = trim((string) $input->getOption('tenant'));
            $name = trim((string) $input->getOption('name'));

            if ('' === $tenant || '' === $name) {
                throw new \InvalidArgumentException('--tenant and --name are required.');
            }

            $payload = $this->decodeObject((string) $input->getOption('payload'));
            $payload['nameEntity'] = $name;
            $payload['locale'] = (string) $input->getOption('locale');
            $payload['weight'] = (int) $input->getOption('weight');

            if ('' !== trim((string) $input->getOption('slug'))) {
                $payload['slug'] = trim((string) $input->getOption('slug'));
            }

            $result = $this->useCase->execute(new CreateTag($tenant, $payload));
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
