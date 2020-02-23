<?php

namespace Genesis\BehatApiSpec\Command;

use Behat\Testwork\Cli\Controller;
use Genesis\BehatApiSpec\Context\ApiSpecContext;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateSnapshots implements Controller
{
    /**
     * Configures command to be executable by the controller.
     *
     * @param SymfonyCommand $command
     */
    public function configure(SymfonyCommand $command)
    {
        $command->addOption('--update-snapshots', 'u', InputOption::VALUE_NONE, 'Update failing snapshots automatically.');
    }

    /**
     * Executes controller.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return null|integer
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        if ($input->getOption('update-snapshots')) {
            ApiSpecContext::setUpdateSnapshots(true);
        }
    }
}
