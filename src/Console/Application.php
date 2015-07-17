<?php

namespace JMOlivas\Phpqa\Console;

use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use JMOlivas\Phpqa\Config;

class Application extends BaseApplication
{
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

    public function getApplicationDirectory()
    {
        return __DIR__.'/../../';
    }
}
