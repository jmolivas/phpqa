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
        'source' => 'php/config.yml',
        'destination' => 'php/config.yml',
      ],
      [
        'source' => '.php_cs',
        'destination' => '.php_cs',
      ],
      [
        'source' => 'config.yml',
        'destination' => 'config.yml',
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
              'override',
              null,
              InputOption::VALUE_NONE,
              'Override files on user home directory'
          )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $application = $this->getApplication();
        $config = $application->getConfig();
        $customConfigDirectory = $config->getUserHomeDirectory().'/.phpqa';
        $baseConfigDirectory = $config->getBaseConfigDirectory();

        $override = false;
        if ($input->hasOption('override')) {
            $override = $input->getOption('override');
        }

        $index = 1;
        foreach ($this->files as $file) {
            $source = $baseConfigDirectory.$file['source'];
            $destination = $customConfigDirectory.'/'.$file['destination'];
            if ($this->copyFile($source, $destination, $override)) {
                $output->writeln(sprintf(
                    '<info>%s</info> - <comment>%s</comment>',
                    $index,
                    $destination
                ));
            }
        }
    }

    public function copyFile($source, $destination, $override)
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
