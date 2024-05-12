<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:update', description: 'Updates the Application')]
class UpdateCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if ($version = $input->getArgument('version')) {
            // checkout new version
            shell_exec("sudo git fetch && sudo git checkout " . $version);
        }

        $status = null;

        $status = shell_exec("sudo php bin/console doctrine:migrations:migrate");
        $output->writeln($status);
        if(empty($status)){
            return Command::FAILURE;
        }

        $status = shell_exec("sudo npm run build");
        $output->writeln($status);
        if(empty($status)){
            return Command::FAILURE;
        }

        $status = shell_exec("sudo php bin/console cache:clear");
        $output->writeln($status);
        if(empty($status)){
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    protected function configure()
    {
        $this
            ->addArgument('version', InputArgument::OPTIONAL, "Git Version to update to.");
    }
}