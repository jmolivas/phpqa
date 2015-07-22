<?php

namespace JMOlivas\Phpqa\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class InitCommand extends Command
{
    private $files = [
      [
        'source' => '.php_cs',
        'destination' => '.php_cs',
      ],
      [
        'source' => 'drupal/config.yml',
        'destination' => 'drupal/config.yml',
      ],
      [
        'source' => 'php/config.yml',
        'destination' => 'php/config.yml',
      ],
      [
        'source' => 'symfony/config.yml',
        'destination' => 'symfony/config.yml',
      ],
      [
        'source' => 'messages.yml',
        'destination' => 'messages.yml',
      ],
      [
        'source' => 'phpunit.xml.dist',
        'destination' => 'phpunit.xml.dist',
      ],
    ];

    protected function configure()
    {
        $this
            ->setName('init')
            ->setDescription('Copy configuration files to user home directory')
            ->addOption(
                'dir',
                null,
                InputOption::VALUE_REQUIRED,
                'Directory to copy file(s) valid options home, current'
            )
            ->addOption(
                'override',
                null,
                InputOption::VALUE_NONE,
                'Override files on directory'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $application = $this->getApplication();
        $config = $application->getConfig();

        $dir = $input->getOption('dir');

        if (!$dir) {
            throw new \Exception(
                'You must provide a valid dir value (home or current)'
            );
        }

        $override = false;
        if ($input->hasOption('override')) {
            $override = $input->getOption('override');
        }

        if ($dir === 'current') {
            $this->copyCurrentDirectory($output, $config, $override);
        }

        if ($dir === 'home') {
            $this->copyHomeDirectory($output, $config, $override);
        }
    }

    private function copyCurrentDirectory($output, $config, $override)
    {
        $baseConfigDirectory = $config->getBaseConfigDirectory();
        $currentDirectory = $config->getApplicationDirectory();

        $source = $baseConfigDirectory.'/../phpqa.yml';
        $destination = $currentDirectory.'phpqa.yml';

        if ($this->copyFile($source, $destination, $override)) {
            $output->writeln('Copied file(s):');
            $output->writeln(
                sprintf(
                    '<info>1</info> - <comment>%s</comment>',
                    $destination
                )
            );
        }
    }

    private function copyHomeDirectory($output, $config, $override)
    {
        $baseConfigDirectory = $config->getBaseConfigDirectory();
        $customConfigDirectory = $config->getUserHomeDirectory().'/.phpqa';

        if (!is_dir($customConfigDirectory)) {
            mkdir($customConfigDirectory);
        }

        $index = 1;
        $copiedMessage = true;
        foreach ($this->files as $file) {
            $source = $baseConfigDirectory.$file['source'];
            $destination = $customConfigDirectory.'/'.$file['destination'];
            if ($this->copyFile($source, $destination, $override)) {
                if ($copiedMessage) {
                    $output->writeln('Copied file(s):');
                }
                $copiedMessage = false;
                $output->writeln(
                    sprintf(
                        '<info>%s</info> - <comment>%s</comment>',
                        $index,
                        $destination
                    )
                );
                $index++;
            }
        }
    }

    private function copyFile($source, $destination, $override)
    {
        if (file_exists($destination) && !$override) {
            return false;
        }

        $filePath = dirname($destination);
        if (!is_dir($filePath)) {
            mkdir($filePath);
        }

        return copy(
            $source,
            $destination
        );
    }
}
