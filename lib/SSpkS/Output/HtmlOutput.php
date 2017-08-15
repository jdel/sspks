<?php

namespace SSpkS\Output;

use \Mustache_Engine;
use \Mustache_Loader_FilesystemLoader;
use \Mustache_Logger_StreamLogger;

class HtmlOutput
{
    private $config;
    private $mustache;
    private $tplVars;
    private $template;

    public function __construct(\SSpkS\Config $config)
    {
        $this->config = $config;
        $tplBase  = $this->config->basePath . DIRECTORY_SEPARATOR . $this->config->paths['themes'];
        $tplBase .= $this->config->site['theme'] . DIRECTORY_SEPARATOR . 'templates';

        $this->mustache = new Mustache_Engine(array(
            'loader'          => new Mustache_Loader_FilesystemLoader($tplBase),
            'partials_loader' => new Mustache_Loader_FilesystemLoader($tplBase . '/partials'),
            'charset'         => 'utf-8',
            'logger'          => new Mustache_Logger_StreamLogger('php://stderr'),
        ));

        $this->setVariable('siteName', $this->config->site['name']);
        $this->setVariable('baseUrl', $this->config->baseUrl);
        $this->setVariable('baseUrlRelative', $this->config->baseUrlRelative);
        $this->setVariable('themeUrl', $this->config->baseUrlRelative . $this->config->paths['themes'] . $this->config->site['theme'] . '/');
        $this->setVariable('requestUri', $_SERVER['REQUEST_URI']);
        $this->setVariable('commitHash', $this->config->SSPKS_COMMIT);
        $this->setVariable('branch', $this->config->SSPKS_BRANCH);
    }

    /**
     * @param string $name
     * @param mixed $value
     */
    public function setVariable($name, $value)
    {
        $this->tplVars[$name] = $value;
    }

    /**
     * @param string $tplName
     */
    public function setTemplate($tplName)
    {
        $this->template = $tplName;
    }

    public function output()
    {
        $tpl = $this->mustache->loadTemplate($this->template);
        echo $tpl->render($this->tplVars);
    }
}
