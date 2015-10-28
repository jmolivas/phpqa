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

    public function __construct($name = 'UNKNOWN', $version = 'UNKNOWN')
    {
        parent::__construct($name, $version);
        $this->config = new Config();
    }

    /**
     * @return \JMOlivas\Phpqa\Config
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @return string
     */
    public function getApplicationDirectory()
    {
        return __DIR__ . '/../../';
    }
}
