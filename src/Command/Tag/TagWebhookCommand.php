<?php

declare(strict_types=1);

namespace App\Tagging\Command\Tag;

use App\Tagging\Service\Core\Webhook\TagWebhookRegistry;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'tag:webhook', description: 'List or add Tagging webhooks.')]
final class TagWebhookCommand extends AbstractTagCommand
{
    public function __construct(private readonly TagWebhookRegistry $registry)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('operation', InputArgument::REQUIRED, 'list or add')
            ->addArgument('url', InputArgument::OPTIONAL, 'Webhook URL.')
            ->addOption('secret', null, InputOption::VALUE_REQUIRED, 'Optional signing secret.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $operation = (string) $input->getArgument('operation');

            if ('list' === $operation) {
                $this->writeJson($io, ['ok' => true, 'items' => $this->registry->list()]);

                return self::SUCCESS;
            }

            if ('add' === $operation) {
                $url = trim((string) $input->getArgument('url'));
                if ('' === $url) {
                    throw new \InvalidArgumentException('url argument is required for add.');
                }

                $this->registry->add(
                    $url,
                    null !== $input->getOption('secret') ? (string) $input->getOption('secret') : null,
                );
                $this->writeJson($io, ['ok' => true, 'url' => $url]);

                return self::SUCCESS;
            }

            throw new \InvalidArgumentException('operation must be list or add.');
        } catch (\Throwable $error) {
            return $this->fail($io, $error);
        }
    }
}
