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
        if (array_key_exists('SSPKS_COMMIT', $_ENV)){
            $this->setVariable('commitHash', $_ENV['SSPKS_COMMIT']);
        }
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
