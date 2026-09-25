<?php

declare(strict_types=1);

namespace App\Tagging\Command\Console;

use App\Tagging\Service\Core\Metric\TagMetrics;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'tag:status', description: 'Show Tagging runtime status or metrics.')]
final class TagStatusCommand extends TagAbstractCommand
{
    protected function configure(): void
    {
        $this->addOption('metrics', null, InputOption::VALUE_NONE, 'Render metrics instead of status.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            if ($input->getOption('metrics')) {
                TagMetrics::inc('tag_cli_status');
                $io->writeln(TagMetrics::render());

                return self::SUCCESS;
            }

            $this->writeJson($io, [
                'ok' => true,
                'service' => 'tagging',
                'surface' => 'symfony-console',
                'rootEntity' => 'App\\Tagging\\Entity\\Tag\\TagEntity',
            ]);

            return self::SUCCESS;
        } catch (\Throwable $error) {
            return $this->fail($io, $error);
        }
    }
}
