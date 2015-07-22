<?php

/**
 * @file
 * Contains \JMOlivas\Phpqa\Config.
 */

namespace JMOlivas\Phpqa;

use Symfony\Component\Yaml\Parser;

class Config
{
    /**
     * @var array
     */
    protected $config;

    /**
     * @var boolean
     */
    protected $custom;

    public function __construct()
    {
        $this->custom = false;
        $this->config = [];
        $this->loadFile(__DIR__.'/../'.'phpqa.yml');
        if ($this->getApplicationConfigFile()) {
            $this->loadFile($this->getApplicationConfigFile());
            $this->custom = true;
        }
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

    public function getApplicationDirectory()
    {
        return getcwd() . '/';
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

    public function getApplicationConfigFile()
    {
        $configFile = $this->getApplicationDirectory() .'phpqa.yml';
        if (file_exists($configFile)) {
            return $configFile;
        }

        $configFile = $this->getApplicationDirectory() .'phpqa.yml.dist';
        if (file_exists($configFile)) {
            return $configFile;
        }

        return null;
    }

    public function loadProjectConfiguration($project)
    {
        if ($this->isCustom()) {
            return;
        }

        $configFile = $this->getUserConfigDirectory().$project.'/config.yml';
        if (file_exists($configFile)) {
            $this->loadFile($configFile);

            return;
        }

        $configFile = $this->getBaseConfigDirectory().$project.'/config.yml';
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

        $configFile = $this->getApplicationDirectory().$analyserConfigFile;
        if (file_exists($configFile)) {
            return '--'.$analyserConfigOption.'='.$configFile;
        }

        $configFile = __DIR__.'/../'.$analyserConfigFile;
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

    /**
     * @return boolean
     */
    public function isCustom()
    {
        return $this->custom;
    }
}
