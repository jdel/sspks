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
 * @property string SSPKS_COMMIT current commit hash taken from ENV variables
 * @property string SSPKS_BRANCH current branch taken from ENV variables
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
        
        /** Init variables that are not actual config variables */
        $config['SSPKS_COMMIT'] = '';
        $config['SSPKS_BRANCH'] = '';
        
        /** Override config values with environment variables if present */
        if ($this->envVarIsNotEmpty('SSPKS_COMMIT')) {
            $config['SSPKS_COMMIT'] = $_ENV['SSPKS_COMMIT'];
        }
        
        if ($this->envVarIsNotEmpty('SSPKS_BRANCH')) {
            $config['SSPKS_BRANCH'] = $_ENV['SSPKS_BRANCH'];
        }
        
        if ($this->envVarIsNotEmpty('SSPKS_SITE_NAME')) {
            $config['site']['name'] = $_ENV['SSPKS_SITE_NAME'];
        }
        
        if ($this->envVarIsNotEmpty('SSPKS_SITE_THEME')) {
            $config['site']['theme'] = $_ENV['SSPKS_SITE_THEME'];
        }
        
        if ($this->envVarIsNotEmpty('SSPKS_SITE_REDIRECTINDEX')) {
            $config['site']['redirectindex'] = $_ENV['SSPKS_SITE_REDIRECTINDEX'];
        }
        
        if ($this->envVarIsNotEmpty('SSPKS_PACKAGES_FILE_MASK')) {
            $config['packages']['file_mask'] = $_ENV['SSPKS_PACKAGES_FILE_MASK'];
        }

        if ($this->envVarIsNotEmpty('SSPKS_PACKAGES_MAINTAINER')) {
            $config['packages']['maintainer'] = $_ENV['SSPKS_PACKAGES_MAINTAINER'];
        }

        if ($this->envVarIsNotEmpty('SSPKS_PACKAGES_MAINTAINER_URL')) {
            $config['packages']['maintainer_url'] = $_ENV['SSPKS_PACKAGES_MAINTAINER_URL'];
        }

        if ($this->envVarIsNotEmpty('SSPKS_PACKAGES_DISTRIBUTOR')) {
            $config['packages']['distributor'] = $_ENV['SSPKS_PACKAGES_DISTRIBUTOR'];
        }

        if ($this->envVarIsNotEmpty('SSPKS_PACKAGES_DISTRIBUTOR_URL')) {
            $config['packages']['distributor_url'] = $_ENV['SSPKS_PACKAGES_DISTRIBUTOR_URL'];
        }

        if ($this->envVarIsNotEmpty('SSPKS_PACKAGES_SUPPORT_URL')) {
            $config['packages']['support_url'] = $_ENV['SSPKS_PACKAGES_SUPPORT_URL'];
        }

        $this->config = $config;
        $this->config['basePath'] = $this->basePath;
    }
    
    /**
     * Checks wether an env variable exists and is not an empty string.
     *
     * @param string $name Name of requested environment variable.
     * @return boolean value.
     */
    public function envVarIsNotEmpty($name)
    {
        return (array_key_exists($name, $_ENV) && $_ENV[$name]);
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
