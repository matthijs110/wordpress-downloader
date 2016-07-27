<?php

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class InstallCommand extends Command
{
    protected function configure()
    {
        /**
         * TODO: Version specific installation (Argument).
         */

        $this->setName('install')
             ->setDescription('Install the latest version of WordPress.'); // wordpress install
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Check if a Wordpress installation exists.
        if (
            file_exists($_SERVER["PWD"].'/wp-config-sample.php') || 
            file_exists($_SERVER["PWD"].'/wp-config.php')
        ) {
            return $output->writeLn('<error>WordPress installation found in this directory.</error>');
        } else {
            $output->writeLn('WordPress installation DOES NOT exists.');
        }
        
        // Execute a wget command to install the latest version of WordPress.
        
        $output->writeLn('<info>Downloading the WordPress files..</info>');
        exec("wget https://wordpress.org/latest.tar.gz 2>&1", $response, $return);
        if ($return) {
            $output->writeLn('<error>Something went wrong while downloading the WordPress files.</error>');
            
            foreach($response as $message) {
                $output->writeLn($message);
            }
            return;
        }

        $output->writeLn('<info>Extracting the WordPress files..</info>');
        exec("tar xfz latest.tar.gz --strip-components=1") || ! exec("rm -f latest.tar.gz", $response, $return);
        if ($return) {
            $output->writeLn('<error>Something went wrong while extracting the WordPress files.</error>');

            foreach($response as $message) {
                $output->writeLn($message);
            }
            return;
        }
        
        // Return feedback messages.
        return $output->writeLn('<info>A fresh WordPress installation has been served!</info>');
    }

}
