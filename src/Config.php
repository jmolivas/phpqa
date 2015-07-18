<?php

/**
 * @file
 * Contains \JMOlivas\Phpqa\Config.
 */

namespace JMOlivas\Phpqa;

use Symfony\Component\Yaml\Parser;

class Config
{
    protected $config;

    public function __construct()
    {
        $this->config = [];
        $this->loadFile(__DIR__.'/../'.'config.yml');
        $this->loadFile($this->getBaseConfigDirectory().'messages.yml');
        $this->loadFile($this->getUserHomeDirectory().'messages.yml');
    }

    private function loadFile($file = null)
    {
        if (file_exists($file)) {
            $parser = new Parser();
            $config = $parser->parse(file_get_contents($file));
        }

        if ($config) {
            $this->config = array_replace_recursive($this->config, $config);
        }
    }

    public function get($key, $default = '')
    {
        if (!$key) {
            return $default;
        }

        $config = $this->config;
        $items = explode('.', $key);

        if (!$items) {
            return $default;
        }
        foreach ($items as $item) {
            if (empty($config[$item])) {
                return $default;
            }
            $config = $config[$item];
        }

        return $config;
    }

    public function getBaseConfigDirectory()
    {
        return __DIR__.'/../config/';
    }

    public function getUserConfigDirectory()
    {
        return $this->getUserHomeDirectory().'/.phpqa/';
    }

    public function getUserHomeDirectory()
    {
        return rtrim(getenv('HOME') ?: getenv('USERPROFILE'), '/\\');
    }

    public function loadProjectConfiguration($project)
    {
        $configFile = $this->getUserConfigDirectory().$project.'/config.yml';
        if (file_exists($configFile)) {
            $this->loadFile($configFile);

            return;
        }

        $configFile = $this->getBaseConfigDirectory().$project.'.yml';
        if (file_exists($configFile)) {
            $this->loadFile($configFile);

            return;
        }
    }

    public function getProjectAnalyzerConfigFile($project, $analyzer)
    {
        $analyserConfig = $this->get('application.analyzer.'.$analyzer.'.file');

        if (!is_array($analyserConfig)) {
            return;
        }

        $analyserConfigOption = key($analyserConfig);
        $analyserConfigFile = current($analyserConfig);

        $configFile = __DIR__.'/../'.$analyserConfigFile;
        if (file_exists($configFile)) {
            return '--'.$analyserConfigOption.'='.$configFile;
        }

        $configFile = getcwd().'/'.$analyserConfigFile;
        if (file_exists($configFile)) {
            return '--'.$analyserConfigOption.'='.$configFile;
        }

        $configFile = $this->getUserConfigDirectory().$project.'/'.$analyserConfigFile;
        if (file_exists($configFile)) {
            return '--'.$analyserConfigOption.'='.$configFile;
        }

        $configFile = $this->getUserConfigDirectory().$analyserConfigFile;
        if (file_exists($configFile)) {
            return '--'.$analyserConfigOption.'='.$configFile;
        }

        $configFile = $this->getBaseConfigDirectory().$analyserConfigFile;
        if (file_exists($configFile)) {
            return '--'.$analyserConfigOption.'='.$configFile;
        }

        return '--'.$analyserConfigOption.'='.$analyserConfigFile;
    }

    public function getConfigData()
    {
        return $this->config;
    }
}
