<?php

namespace JMOlivas\Phpqa\Command;

use Exception;
use JMOlivas\Phpqa\Input\FilesOption;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use JMOlivas\Phpqa\Style\SymfonyStyle;
use Symfony\Component\Process\ProcessBuilder;

/**
 * Class AnalyzeCommand
 *
 * @package JMOlivas\Phpqa\Command
 */
class AnalyzeCommand extends Command
{
    /**
     * @var string
     */
    private $needle = '/(\.php)|(\.inc)$/';

    /**
     * @var
     */
    private $directory;

    /**
     * @var array
     */
    private $projects = [
        'php',
        'symfony',
        'drupal'
    ];

    private $analyzerBinaries = [
        'parallel-lint' => 'jakub-onderka/php-parallel-lint/parallel-lint',
        'pdepend' => 'pdepend/pdepend/src/bin/pdepend',
        'php-cs-fixer' => 'fabpot/php-cs-fixer/php-cs-fixer',
        'phpcbf' =>'squizlabs/php_codesniffer/scripts/phpcbf',
        'phpcpd' => 'sebastian/phpcpd/phpcpd',
        'phpcs' => 'squizlabs/php_codesniffer/scripts/phpcs',
        'phpdcd' => 'sebastian/phpdcd/phpdcd',
        'phploc' => 'phploc/phploc/phploc',
        'phpmd' => 'phpmd/phpmd/src/bin/phpmd',
        'phpunit' => 'phpunit/phpunit/phpunit'
    ];

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('analyze')
            ->setDescription('Analyze code')
            ->addOption(
                'project',
                null,
                InputOption::VALUE_OPTIONAL,
                sprintf(
                    'Project name must be (%s) or could be empty if a phpqa.yml or phpqa.yml.dist exists at current directory.',
                    implode(',', $this->projects)
                )
            )
            ->addOption(
                'files',
                null,
                InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
                'File(s) to analyze'
            )->addOption(
                'git',
                null,
                InputOption::VALUE_NONE,
                'All files added to git index will be analyzed.'
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $project = $input->getOption('project');

        /**
         * @var \JMOlivas\Phpqa\Console\Application $application
         */
        $application = $this->getApplication();

        /**
         * @var \JMOlivas\Phpqa\Utils\Config $config
         */
        $config = $application->getConfig();

        if (!$config->isCustom() && !$project) {
            throw new Exception(
                sprintf(
                    'No local phpqa.yml or phpqa.yml.dist at current working directory ' .
                    'you must provide a valid project value (%s)',
                    implode(',', $this->projects)
                )
            );
        }

        if (!$config->isCustom() && !in_array($project, $this->projects)) {
            throw new Exception(
                sprintf(
                    'You must provide a valid project value (%s)',
                    implode(',', $this->projects)
                )
            );
        }

        $config->loadProjectConfiguration($project);

        $this->directory = $application->getApplicationDirectory();

        $io->section($application->getName());

        $filesOption = new FilesOption($input->getOption('files'));
        $git = $input->getOption('git');

        if (!$filesOption->isAbsent() && $git) {
            throw new Exception('Options `files` and `git` cannot be used in combination.');
        }

        if ($filesOption->isAbsent() && !$git) {
            throw new Exception('You must set `files` or `git` options.');
        }

        if (!$filesOption->isAbsent() && $filesOption->isEmpty()) {
            throw new Exception('Options `files` needs at least one file.');
        }

        if ($git) {
            $files = $this->extractCommitedFiles($io, $config);
        } else {
            $files = $filesOption->normalize();
        }

        $io->info($config->get('application.messages.files.info'));
        $io->listing($files);
        $this->checkComposer($io, $files, $config);
        $analyzers = array_keys($config->get('application.analyzer'));

        foreach ($analyzers as $analyzer) {
            $this->analyzer($io, $analyzer, $files, $config, $project);
        }

        $io->writeln(
            sprintf(
                '<info>%s</info>',
                $config->get('application.messages.completed.info')
            )
        );
    }

    /**
     * @param $io
     * @param $config
     * @return array
     */
    private function extractCommitedFiles(SymfonyStyle $io, $config)
    {
        $io->info($config->get('application.messages.git.info'));

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

    /**
     * @param $io
     * @param $files
     * @param $config
     * @throws \Exception
     */
    private function checkComposer(SymfonyStyle $io, $files, $config)
    {
        if (!$config->get('application.method.composer.enabled')) {
            return;
        }

        $io->info($config->get('application.messages.composer.info'));

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
                throw new Exception($config->get('application.messages.composer.error'));
            }

            $io->error($config->get('application.messages.composer.error'));
        }
    }

    /**
     * @param $io
     * @param $analyzer
     * @param $files
     * @param $config
     * @param $project
     * @throws \Exception
     */
    private function analyzer(SymfonyStyle $io, $analyzer, $files, $config, $project)
    {
        if (!$config->get('application.analyzer.'.$analyzer.'.enabled', false)) {
            return;
        }

        $analyzerBinary = $this->calculateBinary($analyzer);

        $configFile = $config->getProjectAnalyzerConfigFile($project, $analyzer);

        $exception = $config->get('application.analyzer.'.$analyzer.'.exception', false);
        $options = $config->get('application.analyzer.'.$analyzer.'.options', []);
        $arguments = $config->get('application.analyzer.'.$analyzer.'.arguments', []);
        $prefixes = $config->get('application.analyzer.'.$analyzer.'.prefixes', []);
        $postfixes = $config->get('application.analyzer.'.$analyzer.'.postfixes', []);

        $success = true;

        $io->info($config->get('application.messages.'.$analyzer.'.info'));

        $processArguments = [
            'php',
            $this->directory.$analyzerBinary
        ];

        if ($configFile) {
            $singleExecution = $config->get('application.analyzer.'.$analyzer.'.file.single-execution');

            if ($singleExecution) {
                $process = $this->executeProcess(
                    $io,
                    $processArguments,
                    $configFile,
                    $prefixes,
                    $postfixes,
                    $arguments,
                    $options
                );
                $success = $process->isSuccessful();
                $files = [];
            }

            $processArguments[] = $configFile;
        }

        foreach ($files as $file) {
            if (!preg_match($this->needle, $file) && !is_dir(realpath($this->directory.$file))) {
                continue;
            }

            $process = $this->executeProcess(
                $io,
                $processArguments,
                $file,
                $prefixes,
                $postfixes,
                $arguments,
                $options
            );

            if ($success) {
                $success = $process->isSuccessful();
            }
        }

        if ($exception && !$success) {
            throw new Exception($config->get('application.messages.'.$analyzer.'.error'));
        }
    }

    /**
     * @param $io
     * @param $processArguments
     * @param $file
     * @param $prefixes
     * @param $postfixes
     * @param $arguments
     * @param $options
     * @return \Symfony\Component\Process\Process
     */
    public function executeProcess(SymfonyStyle $io, $processArguments, $file, $prefixes, $postfixes, $arguments, $options)
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
            $io->error(trim($process->getErrorOutput()));
        }

        if ($process->getOutput()) {
            $io->writeln($process->getOutput());
        }

        return $process;
    }

    /**
     * @param $analyzer
     * @throws \Exception
     */
    private function calculateBinary($analyzer)
    {
        $binaries = [
            '/bin/'.$analyzer
        ];

        if (array_key_exists($analyzer, $this->analyzerBinaries)) {
            $binaries[] = '/../../'.$this->analyzerBinaries[$analyzer];
            $binaries[] = '/../../../'.$this->analyzerBinaries[$analyzer];
        }

        foreach ($binaries as $binary) {
            if (file_exists($this->directory.$binary)) {
                return $binary;
            }
        }

        throw new Exception(
            sprintf('%s do not exist!', $analyzer)
        );
    }
}
