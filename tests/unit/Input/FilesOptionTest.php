<?php

namespace JMOlivas\Phpqa\Input;

use PHPUnit_Framework_TestCase as TestCase;

class FilesOptionTest extends TestCase
{
    /** @test */
    function it_should_recognize_if_option_is_absent()
    {
        $absentInput = [];
        $files = new FilesOption($absentInput);

        $this->assertTrue($files->isAbsent());
    }

    /** @test */
    function it_should_recognize_if_option_is_provided_but_is_empty()
    {
        $emptyInput = [null];
        $files = new FilesOption($emptyInput);

        $this->assertTrue($files->isEmpty());
    }

    /** @test */
    function it_should_recognize_if_option_is_provided_correctly()
    {
        $validInput = ['src/'];
        $files = new FilesOption($validInput);

        $this->assertFalse($files->isAbsent());
        $this->assertFalse($files->isEmpty());
    }

    /** @test */
    function it_should_normalize_input_separated_by_commas()
    {
        // bin/phpqa analyze --files=src/,test/
        $singleInputWithMultipleValues = ['src/,test/'];
        $files = new FilesOption($singleInputWithMultipleValues);

        $values = $files->normalize();

        $this->assertCount(2, $values);
        $this->assertEquals('src/', $values[0]);
        $this->assertEquals('test/', $values[1]);
    }

    /** @test */
    function it_should_return_multiple_files_input_as_is()
    {
        // bin/phpqa analyze --files=src/ --files=test/
        $singleInputWithMultipleValues = ['src/','test/'];
        $files = new FilesOption($singleInputWithMultipleValues);

        $values = $files->normalize();

        $this->assertCount(2, $values);
        $this->assertEquals('src/', $values[0]);
        $this->assertEquals('test/', $values[1]);
    }

    /** @test */
    function it_should_return_empty_array_if_input_is_absent()
    {
        $absentInput = [];
        $files = new FilesOption($absentInput);

        $this->assertCount(0, $files->normalize());
    }

    /** @test */
    function it_should_return_empty_array_if_input_is_empty()
    {
        $emptyInput = [null];
        $files = new FilesOption($emptyInput);

        $this->assertCount(0, $files->normalize());
    }
}
