<?php

namespace JMOlivas\Phpqa\Command;

use JMOlivas\Phpqa\Console\Application;
use PHPUnit_Framework_TestCase as TestCase;
use Symfony\Component\Console\Tester\CommandTester;

class AnalyzeCommandTest extends TestCase
{
    /**
     * @test
     * @expectedException \Exception
     * @expectedExceptionMessage You must set `files` or `git` options.
     */
    function it_should_throw_exception_if_neither_files_nor_git_options_are_provided()
    {
        $application = new Application();
        $command = new AnalyzeCommand();
        $command->setApplication($application);

        $tester = new CommandTester($command);

        $tester->execute([]);
    }

    /**
     * @test
     * @expectedException \Exception
     * @expectedExceptionMessage Options `files` and `git` cannot be used in combination.
     */
    function it_should_throw_exception_if_both_files_and_git_options_are_provided()
    {
        $application = new Application();
        $command = new AnalyzeCommand();
        $command->setApplication($application);

        $tester = new CommandTester($command);

        $tester->execute([
            '--files' => [null],
            '--git' => true
        ]);
    }

    /**
     * @test
     * @expectedException \Exception
     * @expectedExceptionMessage Options `files` needs at least one file.
     */
    function it_should_throw_exception_if_files_is_provided_but_it_is_empty()
    {
        $application = new Application();
        $command = new AnalyzeCommand();
        $command->setApplication($application);

        $tester = new CommandTester($command);

        $tester->execute([
            '--files' => [null],
        ]);
    }
}
