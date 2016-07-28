<?php

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class InstallCommand extends Command
{
    /**
     * Configure the command.
     *
     * @return void
     */
    protected function configure()
    {
        /**
         * TODO: Version specific installation (Argument).
         */

        $this->setName('install')
             ->setDescription('Install the latest version of WordPress.');
    }

    /**
     * Execute the command.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return mixed
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->checkForExistingWordPressInstallation();
             
        $output->writeLn('<info>Downloading the WordPress files...</info>');
        
        $commands = [
            'curl -o latest.tar.gz https://wordpress.org/latest.tar.gz --progress-bar',
            'tar xfz latest.tar.gz --strip-components=1',
            'rm -f latest.tar.gz',
        ];

        $process = new Process(implode(' && ', $commands));

        $process->run(function ($type, $line) use ($output) {
            $output->write($line);
        });

        if (! $process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
         
        // Return feedback messages.
        $output->writeLn('<comment>A fresh WordPress installation has been served!</comment>');
        $output->writeLn('<info>Visit your installation directory to configure WordPress.</info>');
    }

    /**
     * Check if a WordPress installtion exists already.
     *
     * @return mixed
     */
    private function checkForExistingWordPressInstallation()
    {
        if (
            file_exists($_SERVER["PWD"].'/wp-config-sample.php') || 
            file_exists($_SERVER["PWD"].'/wp-config.php')
        ) {
            throw new RuntimeException('WordPress installation found in this directory.');
        }
    }

}
