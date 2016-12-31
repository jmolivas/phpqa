<?php

/**
 * @file
 * Contains \JMOlivas\Phpqa\Console\Application.
 */

namespace JMOlivas\Phpqa;

use Symfony\Component\Console\Application as BaseApplication;
use JMOlivas\Phpqa\Utils\Config;

/**
 * Class Application
 *
 * @package JMOlivas\Phpqa\Console
 */
class Application extends BaseApplication
{
    /**
     * @var \JMOlivas\Phpqa\Config
     */
    private $config;

    public function __construct()
    {
        parent::__construct('PHP QA Analyzer', '0.1.0');
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
        return __DIR__ . '/../';
    }
}
