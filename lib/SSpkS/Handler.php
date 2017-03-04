<?php

// Starting point, handle incoming request and hand over to more specific handler

namespace SSpkS;

class Handler
{
    private $config;
    private $handler_list;

    public function __construct(\SSpkS\Config $config)
    {
        $this->config = $config;

        // ordered by priority (top to bottom)
        $this->handler_list = array(
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
        foreach ($this->handler_list as $possible_handler) {
            // Add namespace to class name
            $possible_handler = '\\SSpkS\\Handler\\' . $possible_handler;
            $handler = new $possible_handler($this->config);
            if ($handler->canHandle()) {
                $handler->handle();
                break;
            }
        }
    }
}
