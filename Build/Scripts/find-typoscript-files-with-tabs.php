#!/usr/bin/env php
<?php
declare(strict_types=1);

namespace MbhSoftware;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

require_once __DIR__ . '/../../vendor/autoload.php';

class Command extends \Symfony\Component\Console\Command\Command
{
    protected function configure()
    {
        $this
            ->setName('default')
            ->setDescription('default command')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $process = new Process(
            'git ls-files | egrep \'(.*\.(ts|t3s|t3c|typoscript|tsconfig))|constants.txt|setup.txt\'',
            realpath(__DIR__. '/../../')
        );
        $process->run();

        $files = explode(PHP_EOL, $process->getOutput());

        $this->input = $input;
        $this->output = $output;

        $errors = [];

        foreach ($files as $file) {
            if (!file_exists($file)) {
                continue;
            }

            $fileObject = new \SplFileObject($file);

            $i = 1;
            while (!$fileObject->eof()) {
                $line = $fileObject->fgets();

                if (strpos($line, "\t") !== false) {
                    $errors[$file][str_pad((string)$i, 3, '0', STR_PAD_LEFT)] = $fileObject->fgets();
                }

                $i++;
            }

            if (empty($errors[$file])) {
                $output->write('<fg=green>.</>');
            } else {
                $output->write('<error>F</error>');
            }
        }


        if (!empty($errors)) {
            $output->writeln('');
            $output->writeln('');

            foreach ($errors as $file => $fileErrors) {
                $output->writeln('<error>' . $file . '</error>');

                if ($output->isVerbose()) {
                    /**
                     * @var array $fileErrors
                     * @var int $line
                     * @var string $content
                     */
                    foreach ($fileErrors as $line => $content) {
                        $output->write($line . ': ' . str_replace(["\t"], ['--->'], $content));
                    }

                    $output->writeln('');
                }
            }

            return 1;
        }

        return 0;
    }
}

$application = new Application();
$application->addCommands([new \MbhSoftware\Command()]);
$application->setDefaultCommand('default');
$application->run();
