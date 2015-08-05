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
        'source' => '/../phpqa.yml',
        'destination' => 'php/config.yml',
      ],
      [
        'source' => '/../phpqa.yml',
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

    private $projects = [
      'php',
      'symfony',
      'drupal'
    ];

    private $dirs = [
      'home',
      'current'
    ];

    protected function configure()
    {
        $this
            ->setName('init')
            ->setDescription('Copy configuration files to user home directory')
            ->addOption(
                'project',
                null,
                InputOption::VALUE_REQUIRED,
                sprintf(
                    'Project name to copy config from, must be (%s).',
                    implode(',', $this->projects)
                )
            )
            ->addOption(
                'global',
                null,
                InputOption::VALUE_NONE,
                'Copy configuration files to user home directory, instead of current working directory.'
            )
            ->addOption(
                'override',
                null,
                InputOption::VALUE_NONE,
                'Copy files using override flag.'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $application = $this->getApplication();
        $config = $application->getConfig();

        $global = false;
        if ($input->hasOption('global')) {
            $global = $input->getOption('global');
        }

        $project = $input->getOption('project');

        if ($global && $project) {
            throw new \Exception('Options `project` and `global` can not used in combination.');
        }

        if (!$global && (!$project || !in_array($project, $this->projects))) {
            throw new \Exception(
                sprintf(
                    'You must provide a valid project value (%s)',
                    implode(',', $this->projects)
                )
            );
        }

        $override = false;
        if ($input->hasOption('override')) {
            $override = $input->getOption('override');
        }

        if ($global) {
            $this->copyHomeDirectory($output, $config, $override);
        }

        if (!$global) {
            $this->copyCurrentDirectory($output, $config, $override, $project);
        }
    }

    private function copyCurrentDirectory($output, $config, $override, $project)
    {
        $baseConfigDirectory = $config->getBaseConfigDirectory();
        $currentDirectory = $config->getApplicationDirectory();

        $source = $baseConfigDirectory.'/../phpqa.yml';

        $configFile = $baseConfigDirectory.$project.'/config.yml';
        if (file_exists($configFile)) {
            $source = $configFile;
        }

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
