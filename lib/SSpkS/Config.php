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
 * @property array packages Defaults for packages
 * @property string basePath Path to site root (where index.php is located)
 * @property string baseUrl URL to site root (where index.php is located)
 * @property string baseUrlRelative Relative URL to site root (without scheme or hostname)
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

        /** Override config values with environment variables if present */
        $config['SSPKS_COMMIT'] = (array_key_exists('SSPKS_COMMIT', $_ENV) && $_ENV['SSPKS_COMMIT'])?$_ENV['SSPKS_COMMIT']:NULL;
        $config['SSPKS_BRANCH'] = (array_key_exists('SSPKS_BRANCH', $_ENV) && $_ENV['SSPKS_BRANCH'])?$_ENV['SSPKS_BRANCH']:NULL;
        
        $config['site']['name'] = (array_key_exists('SSPKS_SITE_NAME', $_ENV) && $_ENV['SSPKS_SITE_NAME'])?$_ENV['SSPKS_SITE_NAME']:$config['site']['name'];
        $config['site']['theme'] = (array_key_exists('SSPKS_SITE_THEME', $_ENV) && $_ENV['SSPKS_SITE_THEME'])?$_ENV['SSPKS_SITE_THEME']:$config['site']['theme'];
        $config['site']['redirectindex'] = (array_key_exists('SSPKS_SITE_REDIRECTINDEX', $_ENV) && $_ENV['SSPKS_SITE_REDIRECTINDEX'])?$_ENV['SSPKS_SITE_REDIRECTINDEX']:$config['site']['redirectindex'];

        $this->config = $config;
        $this->config['basePath'] = $this->basePath;
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
     * Setter magic method.
     *
     * @param string $name Name of variable to set.
     * @param mixed $value Value to set.
     */
    public function __set($name, $value)
    {
        $this->config[$name] = $value;
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

    /**
     * Unset feature magic method.
     *
     * @param string $name Name of value to unset.
     */
    public function __unset($name)
    {
        unset($this->config[$name]);
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
