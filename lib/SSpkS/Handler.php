<?php

// Starting point, handle incoming request and hand over to more specific handler

namespace SSpkS;

class Handler
{
    private $config;
    private $handlerList;

    public function __construct(\SSpkS\Config $config)
    {
        $this->config = $config;

        // ordered by priority (top to bottom)
        $this->handlerList = array(
            'SynologyHandler',
            'BrowserRedirectHandler',
            'BrowserPackageListHandler',
            'BrowserAllPackagesListHandler',
            'BrowserDeviceListHandler',
            'NotFoundHandler'
        );
    }

    public function handle()
    {
        foreach ($this->handlerList as $possibleHandler) {
            // Add namespace to class name
            $possibleHandler = '\\SSpkS\\Handler\\' . $possibleHandler;
            $handler = new $possibleHandler($this->config);
            if ($handler->canHandle()) {
                $handler->handle();
                break;
            }
        }
    }
}
