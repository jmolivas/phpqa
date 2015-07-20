<?php

/**
 * @file
 * Contains \JMOlivas\Phpqa\Console\Application.
 */

namespace JMOlivas\Phpqa\Console;

use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use JMOlivas\Phpqa\Config;

/**
 * Class Application
 * @package JMOlivas\Phpqa\Console
 */
class Application extends BaseApplication
{
    /**
     * @var \JMOlivas\Phpqa\Config
     */
    private $config;

    /**
     * @return \JMOlivas\Phpqa\Config
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * {@inheritdoc}
     */
    public function doRun(InputInterface $input, OutputInterface $output)
    {
        $this->config = new Config();

        parent::doRun($input, $output);
    }

    /**
     * @return string
     */
    public function getApplicationDirectory()
    {
        return __DIR__.'/../../';
    }
}
