<?php

namespace SSpkS;

use \Symfony\Component\Yaml\Yaml;
use \Symfony\Component\Yaml\Exception\ParseException;

/**
 * Configuration class
 *
 * @property array $site Site properties
 * @property array $paths Different paths
 * @property array excludedSynoServices Synology services to exclude from package list
 */
class Config implements \Iterator
{
    private $iterPos;
    private $basePath;
    private $cfgFile;
    private $config;

    public function __construct($basePath, $cfgFile = 'conf/sspks.yaml')
    {
        $this->iterPos  = 0;
        $this->basePath = $basePath;
        $this->cfgFile  = $this->basePath . DIRECTORY_SEPARATOR . $cfgFile;

        if (!file_exists($this->cfgFile)) {
            throw new \Exception('Config file "' . $this->cfgFile . '" not found!');
        }

        try {
            /** @var array $config */
            $config = Yaml::parse(file_get_contents($this->cfgFile));
        } catch (ParseException $e) {
            throw new \Exception($e->getMessage());
        }

        $this->config = $config;
    }

    /**
     * Getter magic method.
     *
     * @param string $name Name of requested value.
     * @return mixed Requested value.
     */
    public function __get($name)
    {
        return $this->config[$name];
    }

    /**
     * Isset feature magic method.
     *
     * @param string $name Name of requested value.
     * @return bool TRUE if value exists, FALSE otherwise.
     */
    public function __isset($name)
    {
        return isset($this->config[$name]);
    }

    public function rewind()
    {
        $this->iterPos = 0;
    }

    public function current()
    {
        return $this->config[array_keys($this->config)[$this->iterPos]];
    }

    public function key()
    {
        return array_keys($this->config)[$this->iterPos];
    }

    public function next()
    {
        $this->iterPos++;
    }

    public function valid()
    {
        return isset(array_keys($this->config)[$this->iterPos]);
    }
}
