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

        /**
         * @var \JMOlivas\Phpqa\Console\Application $application
         */
        $application = $this->getApplication();

        /**
         * @var \JMOlivas\Phpqa\Config $config
         */
        $config = $application->getConfig();
        $config->loadProjectConfiguration($project);

        $this->directory = $application->getApplicationDirectory();

        $output->writeln(sprintf('<question>%s</question>', $application->getName()));

        $files = $input->getOption('files');

        if ($files) {
            $files = explode(',', $files[0]);
        }

        if (!$files) {
            $files = $this->extractCommitedFiles($output, $config);
        }

        $output->writeln(
            sprintf(
                '<info>%s</info>',
                $config->get('application.messages.files.info')
            )
        );

        foreach ($files as $file) {
            $output->writeln(
                sprintf(
                    '<comment> - %s</comment>',
                    $file
                )
            );
        }

        $this->checkComposer($output, $files, $config);

        $analyzers = array_keys($config->get('application.analyzer'));

        foreach ($analyzers as $analyzer) {
            $this->analyzer($output, $analyzer, $files, $config, $project);
        }

        $output->writeln(
            sprintf(
                '<info>%s</info>',
                $config->get('application.messages.completed.info')
            )
        );
    }

    private function extractCommitedFiles($output, $config)
    {
        $output->writeln(
            sprintf(
                '<info>%s</info>',
                $config->get('application.messages.git.info')
            )
        );

        $files = [];
        $result = 0;

        exec('git rev-parse --verify HEAD 2> /dev/null', $files, $result);

        $against = '4b825dc642cb6eb9a060e54bf8d69288fbee4904';
        if ($result == 0) {
            $against = 'HEAD';
        }

        exec("git diff-index --cached --name-status $against | egrep '^(A|M)' | awk '{print $2;}'", $files);

        unset($files[0]);

        return $files;
    }

    private function checkComposer($output, $files, $config)
    {
        if (!$config->get('application.method.composer.enabled')) {
            return;
        }

        $output->writeln(
            sprintf(
                '<info>%s</info>',
                $config->get('application.messages.composer.info')
            )
        );

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

        if ($config->get('application.method.composer.exception')) {
            if ($composerJsonDetected && !$composerLockDetected) {
                throw new \Exception($config->get('application.messages.composer.error'));
            }

            $output->writeln(
                sprintf(
                    '<error> %s</error>',
                    $config->get('application.messages.composer.error')
                )
            );
        }
    }

    private function analyzer($output, $analyzer, $files, $config, $project)
    {
        if (!$config->get('application.analyzer.'.$analyzer.'.enabled', false)) {
            return;
        }

        $this->validateBinary('bin/'.$analyzer);

        $configFile = $config->getProjectAnalyzerConfigFile($project, $analyzer);

        $exception = $config->get('application.analyzer.'.$analyzer.'.exception', false);
        $options = $config->get('application.analyzer.'.$analyzer.'.options', []);
        $arguments = $config->get('application.analyzer.'.$analyzer.'.arguments', []);
        $prefixes = $config->get('application.analyzer.'.$analyzer.'.prefixes', []);
        $postfixes = $config->get('application.analyzer.'.$analyzer.'.postfixes', []);

        $success = true;

        $output->writeln(
            sprintf(
                '<info>%s</info>',
                $config->get('application.messages.'.$analyzer.'.info')
            )
        );

        $processArguments = [
          'php',
          $this->directory.'bin/'.$analyzer
        ];

        if ($configFile) {
            $singleExecution = $config->get('application.analyzer.'.$analyzer.'.file.single-execution');

            if ($singleExecution) {
                $process = $this->executeProcess($output, $processArguments, $configFile, $prefixes, $postfixes, $arguments, $options);
                $success = $process->isSuccessful();
                $files = [];
            }

            $processArguments[] = $configFile;
        }

        foreach ($files as $file) {
            if (!preg_match($this->needle, $file) && !is_dir(realpath($this->directory.$file))) {
                continue;
            }

            $process = $this->executeProcess($output, $processArguments, $file, $prefixes, $postfixes, $arguments, $options);

            if ($success) {
                $success = $process->isSuccessful();
            }
        }

        if ($exception && !$success) {
            throw new \Exception($config->get('application.messages.'.$analyzer.'.error'));
        }
    }

    public function executeProcess($output, $processArguments, $file, $prefixes, $postfixes, $arguments, $options)
    {
        foreach ($prefixes as $prefix) {
            $processArguments[] = $prefix;
        }

        $processArguments[] = $file;

        foreach ($postfixes as $postfix) {
            $processArguments[] = $postfix;
        }

        $processBuilder = new ProcessBuilder($processArguments);

        foreach ($arguments as $argument) {
            $processBuilder->add($argument);
        }

        foreach ($options as $optionName => $optionValue) {
            $processBuilder->setOption($optionName, $optionValue);
        }

        $process = $processBuilder->getProcess();
        $process->run();

        if (!$process->isSuccessful()) {
            $output->writeln(sprintf('<error>%s</error>', trim($process->getErrorOutput())));
        }

        if ($process->getOutput()) {
            $output->writeln($process->getOutput());
        }

        return $process;
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
