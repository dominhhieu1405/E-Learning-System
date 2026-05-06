<?php

namespace Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Command\LockableTrait;
use \Models\Model;


error_reporting(E_ALL);
class UpdateOnline extends Command
{
    use LockableTrait;

    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'app:online';

    protected function configure(): void
    {

    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Update những người có last_seen < 30p
        $update_time = 30;
        Model::getDB()->rawQuery("UPDATE online SET online = 0 WHERE last_seen < NOW() - INTERVAL $update_time MINUTE");
        $output->writeln("Update online success");

        return Command::SUCCESS;
    }

}
