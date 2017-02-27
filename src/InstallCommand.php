<?php

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\{InputOption, InputInterface};
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
        $this->setName('install')
             ->setDescription('Download the latest release of WordPress.')
             ->addOption('release', 'r', InputOption::VALUE_REQUIRED, 'Specify the version of WordPress', 'latest');
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
        $this->checkForCurlInstallation();
        $this->checkForExistingWordPressInstallation();

        $release = trim($input->getOption('release'), "=");
        $file = "wordpress-$release";

        $url = "https://wordpress.org/${file}.tar.gz";
        if (! $this->isValidUrl($url)) {
            throw new RuntimeException("Version ${release} does not exist.");
        }

        $version = explode("-", $file)[1];
        $output->writeLn("<info>Downloading and extracting WordPress ${version}...</info>");

        $commands = [
            "curl -o wordpress.tar.gz ${url} --progress-bar",
            "tar xfz wordpress.tar.gz --strip-components=1",
            "rm -f wordpress.tar.gz",
        ];

        $process = new Process(implode(" && ", $commands));

        $process->run(function ($type, $line) use ($output) {
            $output->write($line);
        });

        if (! $process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        // Return feedback messages.
        $output->writeLn("<comment>A fresh WordPress installation has been downloaded and unpacked!</comment>");
        $output->writeLn("<info>Visit your URL to configure WordPress.</info>");
    }

    /**
     * Check if the current directory contains a WordPress installation.
     *
     * @return mixed
     */
    protected function checkForExistingWordPressInstallation()
    {
        if (file_exists(getcwd()."/wp-settings.php")) {
            throw new RuntimeException("WordPress installation found in this directory.");
        }
    }

    /**
     * Check if cURL is installed.
     *
     * @return mixed
     */
    protected function checkForCurlInstallation()
    {
        if (! `curl --version`) {
            throw new RuntimeException("The required cURL command was not found.");
        }
        return true;
    }

    /**
     * Checks weather the URL exists.
     *
     * @param  string  $url The URL to check.
     * @return boolean
     */
    protected function isValidUrl($url)
    {
        $headers = get_headers($url);
        return stripos($headers[0], "200 OK");
    }
}
