<?php

namespace JMOlivas\Phpqa\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\ProcessBuilder;

class AnalyzeCommand extends Command
{
    /**
     * @var string
     */
    private $needle = '/(\.php)|(\.inc)$/';

    private $directory;

    protected function configure()
    {
        $this
            ->setName('analyze')
            ->setDescription('Analyze code')
            ->addOption(
                'files',
                null,
                InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
                'File(s) to avalyze'
            )
            ->addOption(
                'project',
                null,
                InputOption::VALUE_REQUIRED,
                'Project name'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $project = $input->getOption('project');

        /** @var \JMOlivas\Phpqa\Console\Application $application */
        $application = $this->getApplication();

        /** @var \JMOlivas\Phpqa\Config $config */
        $config = $application->getConfig();
        $config->loadProjectConfiguration($project);

        $this->directory = $application->getApplicationDirectory();

        $output->writeln(sprintf(
            '<question>%s</question>',
            $application->getName()
        ));

        $files = $input->getOption('files');

        if ($files) {
            $files = explode(',', $files[0]);
        }

        if (!$files) {
            $files = $this->extractCommitedFiles($output, $config);
        }

        $output->writeln(sprintf(
            '<info>%s</info>',
            $config->get('application.messages.files.info')
        ));

        foreach ($files as $file) {
            $output->writeln(
                sprintf(
                    '<comment> - %s</comment>',
                    $file
                )
            );
        }

        $this->checkComposer($output, $files, $config);

        $this->analyzer($output, 'parallel-lint', $files, $config);

//        $output->writeln('<info>Checking code style</info>');
//        if (!$this->codeStyle($output, $files)) {
//            throw new \Exception(sprintf('There are coding standards violations!'));
//        }

        $this->analyzer($output, 'php-cs-fixer', $files, $config);

        $output->writeln('<info>Fixing code style with PHPCBF</info>');
        if (!$this->codeStylePsr($output, $files, 'phpcbf')) {
            throw new \Exception(sprintf('There are PHPCS coding standards violations! and some got fixed by PHPCBF'));
        }

        $output->writeln('<info>Checking code style with PHPCS</info>');
        if (!$this->codeStylePsr($output, $files, 'phpcs')) {
            throw new \Exception(sprintf('There are PHPCS coding standards violations!'));
        }

//        $output->writeln('<info>Checking code mess with PHPMD</info>');
//        $this->phPmd($output, $files);

        $output->writeln('<info>Running unit tests</info>');
        if (!$this->unitTests($output, $project, $config)) {
            throw new \Exception('PHPUnit test failed!');
        }

        $output->writeln('<info>Analysis Completed!</info>');
    }

    private function extractCommitedFiles($output, $config)
    {
        $output->writeln(sprintf(
            '<info>%s</info>',
            $config->get('application.messages.git.info')
        ));

        $files = [];
        $rc = 0;

        exec('git rev-parse --verify HEAD 2> /dev/null', $files, $rc);

        $against = '4b825dc642cb6eb9a060e54bf8d69288fbee4904';
        if ($rc == 0) {
            $against = 'HEAD';
        }

        exec("git diff-index --cached --name-status $against | egrep '^(A|M)' | awk '{print $2;}'", $files);

        unset($files[0]);

        return $files;
    }

    private function checkComposer($output, $files, $config)
    {
        if (!$config->get('application.analyzer.composer.enabled')) {
            return;
        }

        $output->writeln(sprintf(
            '<info>%s</info>',
            $config->get('application.messages.composer.info')
        ));

        $composerJsonDetected = false;
        $composerLockDetected = false;

        foreach ($files as $file) {
            if ($file === 'composer.json') {
                $composerJsonDetected = true;
            }

            if ($file === 'composer.lock') {
                $composerLockDetected = true;
            }
        }

        if ($config->get('application.analyzer.composer.exception')) {
            if ($composerJsonDetected && !$composerLockDetected) {
                throw new \Exception($config->get('application.messages.composer.error'));
            }

            $output->writeln(sprintf(
                '<comment> %s</comment>',
                $config->get('application.messages.composer.error')
            ));
        }
    }

    private function analyzer($output, $analyzer, $files, $config)
    {
        $enabled = $config->get('application.analyzer.'.$analyzer.'.enabled');
        if (!$enabled) {
            return;
        }

        $exception = $config->get('application.analyzer.'.$analyzer.'.exception');

        $options = $config->get('application.analyzer.'.$analyzer.'.options');
        $arguments = $config->get('application.analyzer.'.$analyzer.'.arguments');

        if ($arguments) {
            $arguments = array_keys($arguments);
        }

        $success = true;
        $this->validateBinary('bin/'.$analyzer);

        $output->writeln(sprintf(
            '<info>%s</info>',
            $config->get('application.messages.'.$analyzer.'.info')
        ));

        foreach ($files as $file) {
            if (!preg_match($this->needle, $file) && !is_dir(realpath($this->directory.$file))) {
                continue;
            }

            $arguments[] = $file;

            $processBuilder = new ProcessBuilder(['php', $this->directory.'bin/'.$analyzer]);

            if ($arguments) {
                foreach ($arguments as $argument) {
                    $processBuilder->add($argument);
                }
            }

            if ($options) {
                foreach ($options as $optionName => $optionValue) {
                    $processBuilder->setOption($optionName, $optionValue);
                }
            }

            $process = $processBuilder->getProcess();
            $process->run();

            if (!$process->isSuccessful()) {
                $output->writeln(sprintf('<error>%s</error>', trim($process->getErrorOutput())));
                $success = false;
            }

            $output->writeln(sprintf('<comment>%s</comment>', trim($process->getOutput())));
        }

        if ($exception && !$success) {
            throw new \Exception($config->get('application.messages.'.$analyzer.'.error'));
        }
    }

//    private function codeStyle($output, array $files)
//    {
//        $this->validateBinary('bin/php-cs-fixer');
//
//        $succeed = true;
//
//        foreach ($files as $file) {
//            if (!preg_match($this->needle, $file) && !is_dir(realpath($this->directory.$file))) {
//                continue;
//            }
//
//            $processBuilder = new ProcessBuilder(['php', $this->directory.'bin/php-cs-fixer', 'fix', '--verbose', '--level=psr2', $file]);
//
//            $phpCsFixer = $processBuilder->getProcess();
//            $phpCsFixer->run();
//
//            if (!$phpCsFixer->isSuccessful()) {
//                $output->writeln(sprintf('<error>%s</error>', trim($phpCsFixer->getOutput())));
//
//                if ($succeed) {
//                    $succeed = false;
//                }
//            }
//        }
//
//        return $succeed;
//    }

    private function codeStylePsr($output, array $files, $command)
    {
        $this->validateBinary(sprintf('bin/%s', $command));

        $succeed = true;

        foreach ($files as $file) {
            if (!preg_match($this->needle, $file) && !is_dir(realpath($this->directory.$file))) {
                continue;
            }

            $processBuilder = new ProcessBuilder(['php', $this->directory.'bin/'.$command, '--standard=PSR2', '-n', $file]);

            $phpCsFixer = $processBuilder->getProcess();
            $phpCsFixer->run();

            if (!$phpCsFixer->isSuccessful()) {
                $output->writeln(sprintf('<error>%s</error>', trim($phpCsFixer->getOutput())));

                if ($succeed) {
                    $succeed = false;
                }
            }
        }

        return $succeed;
    }

    private function phPmd($output, $files)
    {
        $this->validateBinary('bin/phpmd');

        $succeed = true;

        foreach ($files as $file) {
            if (!preg_match($this->needle, $file) && !is_dir(realpath($this->directory.$file))) {
                continue;
            }

            $processBuilder = new ProcessBuilder(['php', $this->directory.'bin/phpmd', $file, 'text', 'cleancode,codesize,unusedcode,naming,controversial,design']);
            $process = $processBuilder->getProcess();
            $process->run();

            if (!$process->isSuccessful()) {
                $output->writeln($file);
                $output->writeln(sprintf('<info>%s</info>', trim($process->getErrorOutput())));
                $output->writeln(sprintf('<comment>%s</comment>', trim($process->getOutput())));
                if ($succeed) {
                    $succeed = false;
                }
            }
        }

        return $succeed;
    }

    private function unitTests($output, $project, $config)
    {
        $configFile = $config->getProjectAnalyzerConfigFile($project, 'phpunit');

        $this->validateBinary('bin/phpunit');

        $processBuilder = new ProcessBuilder(['php', $this->directory.'bin/phpunit', $configFile]);
        $processBuilder->setTimeout(3600);
        $phpunit = $processBuilder->getProcess();

        $phpunit->run(function ($messageType, $buffer) use ($output) {
            $output->write($buffer);
        });

        return $phpunit->isSuccessful();
    }

    private function validateBinary($binaryFile)
    {
        if (!file_exists($this->directory.$binaryFile)) {
            throw new \Exception(
                sprintf('%s do not exist!', $binaryFile)
            );
        }
    }
}
