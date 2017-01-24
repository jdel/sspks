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

        $this->mustache = new Mustache_Engine(array(
            'loader'          => new Mustache_Loader_FilesystemLoader($this->config->basePath . '/data/templates'),
            'partials_loader' => new Mustache_Loader_FilesystemLoader($this->config->basePath . '/data/templates/partials'),
            'charset'         => 'utf-8',
            'logger'          => new Mustache_Logger_StreamLogger('php://stderr'),
        ));

        $this->setVariable('siteName', $this->config->site['name']);
        $this->setVariable('baseUrl', $this->config->baseUrl);
        $this->setVariable('requestUri', $_SERVER['REQUEST_URI']);
    }

    public function setVariable($name, $value)
    {
        $this->tplVars[$name] = $value;
    }

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
