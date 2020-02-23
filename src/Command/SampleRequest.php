<?php

namespace Genesis\BehatApiSpec\Command;

use Behat\Testwork\Cli\Controller;
use Genesis\BehatApiSpec\Context\ApiSpecContext;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SampleRequest implements Controller
{
    /**
     * Configures command to be executable by the controller.
     *
     * @param SymfonyCommand $command
     */
    public function configure(SymfonyCommand $command)
    {
        $command->addOption('--sample-request', null, InputOption::VALUE_REQUIRED, 'Generate sample request.');
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
        if ($format = $input->getOption('sample-request')) {
            ApiSpecContext::setSampleRequest($format);
        }
    }
}
