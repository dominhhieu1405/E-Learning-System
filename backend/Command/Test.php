<?php

namespace Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Command\LockableTrait;
use \Models\Model;


error_reporting(E_ALL);
class Test extends Command
{
    use LockableTrait;

    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'app:test';

    protected function configure(): void
    {

    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln("Test command executed");

        $data = json_decode(file_get_contents("https://api.vietqr.io/v2/banks"));
        foreach ($data->data as $bank) {
            Model::getDB()->insert("banks", [
                "code" => $bank->code,
                "name" => $bank->name,
                "logo" => $bank->logo,
                "bin" => $bank->bin,
                "short_name" => $bank->short_name,
                "swift_code" => $bank->swift_code,
                //"status" => 1
            ]);
        }
        return Command::SUCCESS;
    }

}
