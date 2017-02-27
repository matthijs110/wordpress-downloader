<?php

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\{InputOption, InputInterface};
use Symfony\Component\Console\Output\OutputInterface;

class InstallCommand extends Command
{
    /**
     * The file to download.
     *
     * @var string
     */
    protected $file;

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
        // Check for ZipArchive and an existing WordPress installation.
        $this->checkForZipArchive();
        $this->checkForExistingWordPressInstallation();

        // Get the right release.
        $release = trim($input->getOption('release'), "=");
        $this->file = "wordpress-$release.zip";

        // Download and unpack WordPress.
        $this->download($output)->unpack($output);

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
        if (file_exists(getcwd()."/wordpress/wp-settings.php")) {
            throw new RuntimeException("WordPress installation found in this directory.");
        }

        return true;
    }

    /**
     * Check if the ZipArchive PHP extension is installed.
     *
     * @return mixed
     */
    protected function checkForZipArchive()
    {
        if (! class_exists("ZipArchive")) {
            throw new RuntimeException("Please install the Zip PHP extension.");
        }

        return true;
    }

    /**
     * Download the Zip from WordPress.
     *
     * @return $this
     */
    protected function download(OutputInterface $output)
    {
        $version = explode(".zip", explode("-", $this->file)[1])[0];
        $output->writeLn("<info>Downloading WordPress ${version}...</info>");

        try {
            $response = (new Client)->get("https://wordpress.org/".$this->file);

            file_put_contents($this->file, $response->getBody());
        } catch (ClientException $ex) {
            throw new RuntimeException("Version ${version} does not exist.");
        }

        return $this;
    }

    /**
     * Extract the Zip file.
     *
     * @return $this
     */
    protected function unpack(OutputInterface $output)
    {
        $output->writeLn("<info>Unpacking WordPress...</info>");
        $archive = new ZipArchive;

        // Open the archive.
        $archive->open($this->file);

        // Extract the content to the current working directory.
        $archive->extractTo(getcwd());

        // Close the archive.
        $archive->close();

        // Delete the zip file.
        $output->writeln('');
        $output->writeLn("<info>Cleaning up...</info>");
        @unlink($this->file);

        return $this;
   }

}
