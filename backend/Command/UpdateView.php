<?php

namespace Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Command\LockableTrait;
use Models\Model;


class UpdateView extends Command
{
    use LockableTrait;

    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'app:view';

    protected function configure(): void
    {
        $this->addArgument('type', InputArgument::OPTIONAL, 'd|w|m|auto', 'auto');

    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {

        $type = $input->getArgument('type');

        $update = [];
        switch ($type) {
            case 'd':
                $update['views_day'] = 0;
                break;
            case 'w':
                $update['views_week'] = 0;
                break;
            case 'm':
                $update['views_month'] = 0;
                break;
            case 'auto':
            default:
                $update['views_day'] = 0;
                if (date("w") == 1)
                    $update['views_week'] = 0;
                if (date("d") == 1)
                    $update['views_month'] = 0;
                break;
        }

        Model::getDB()->update('document', $update);
        Model::getDB()->update('course', $update);
        Model::getDB()->update('lesson', $update);

        $output->writeln("Update view success");

        return Command::SUCCESS;
    }
}