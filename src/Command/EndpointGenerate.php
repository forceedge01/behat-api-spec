<?php

namespace Genesis\BehatApiSpec\Command;

use Behat\Testwork\Cli\Controller;
use Genesis\BehatApiSpec\Extension\Initializer\Initializer;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class EndpointGenerate implements Controller
{
    public function __construct(Initializer $param)
    {
        $this->specMappings = $param->getSpecMappings();
    }

    /**
     * Configures command to be executable by the controller.
     *
     * @param SymfonyCommand $command
     */
    public function configure(SymfonyCommand $command)
    {
        $this->command = $command;
        $command->addOption('--endpoint-generate', null, InputOption::VALUE_NONE, 'Generate an endpoint file.');
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
        if (!$input->getOption('endpoint-generate')) {
            return;
        }

        echo PHP_EOL . '== Generate a new endpoint file. ' . PHP_EOL .
            '(Cancel with ctrl+z or ctrl+c)' . PHP_EOL . PHP_EOL;

        $questionHelper = $this->command->getHelper('question');

        $question = new Question('Endpoint name: ');
        $question->setValidator(function($value) {
            if (empty($value)) {
                throw new \Exception('Name is required');
            }

            return $value;
        });
        $name = $questionHelper->ask($input, $output, $question);

        $question = new Question('Uri (excluding the base url): ');
        $question->setValidator(function($value) {
            if (empty($value)) {
                throw new \Exception('Uri is required');
            }

            return $value;
        });
        $uri = $questionHelper->ask($input, $output, $question);

        // Get the default path from behat.yml
        $defaultNamespace = $this->specMappings['endpoint'];
        $question = new Question('Endpoint namespace (use double backslashes) - default ('. $defaultNamespace .'): ', $defaultNamespace);
        $namespace = trim($questionHelper->ask($input, $output, $question), '\\');

        // Get the default path from behat.yml
        $defaultPath = realpath($this->specMappings['path']);
        $question = new Question('Enter path for endpoint - default ('. $defaultPath .'): ', $defaultPath);
        $path = realpath($questionHelper->ask($input, $output, $question));

        if (!is_dir($path)) {
            throw new \Exception("Path $path not found or is not a directory.");
        }

        $filePath = $path . DIRECTORY_SEPARATOR . $name . '.php';

        if (file_exists($filePath)) {
            throw new \Exception('A file already exists on provided path: ' . $filePath);
        }

        $question = new Question(
            PHP_EOL .
            'Confirm details ' . PHP_EOL . PHP_EOL .
            'Name: ' . $name . PHP_EOL .
            'Uri: ' . $uri . PHP_EOL .
            'Namespace: ' . $namespace. PHP_EOL .
            'Path: ' . $filePath . PHP_EOL . PHP_EOL .
            'Proceed? (y/n)'
        );
        $question->setValidator(function($value) {
            $value = strtolower($value);
            if (!in_array($value, ['y', 'n'])) {
                throw new \Exception('Confirmation value must be either y or n.');
            }

            return $value;
        });
        $confirm = $questionHelper->ask($input, $output, $question);

        if (strtolower($confirm) !== 'y') {
            die('Answered "' . $confirm . '", Aborting...' . PHP_EOL);
        }

        $template = file_get_contents(__DIR__ . '/../Template/Endpoint.php.template');
        $template = str_replace([
            '{{namespace}}',
            '{{name}}',
            '{{uri}}'
        ], [
            $namespace,
            $name,
            $uri
        ], $template);

        file_put_contents($filePath, $template);
        die('Endpoint file generated: ' . $filePath . PHP_EOL);
    }
}
