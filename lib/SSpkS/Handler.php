<?php

// Starting point, handle incoming request and hand over to more specific handler

namespace SSpkS;

use \SSpkS\Handler\BrowserHandler;
use \SSpkS\Handler\NotFoundHandler;
use \SSpkS\Handler\SynologyHandler;

class Handler
{
    private $config;

    public function __construct(\SSpkS\Config $config)
    {
        $this->config = $config;
    }

    public function handle()
    {
        // TODO: Probably walk through all known handlers and query them whether they're
        //       responsible/capable for answering the request or not. Take the best match.

        if (isset($_REQUEST['unique']) && substr($_REQUEST['unique'], 0, 8) == 'synology') {
            $handler = new SynologyHandler($this->config);
        } elseif ($_SERVER['REQUEST_METHOD'] == 'GET') {
            $handler = new BrowserHandler($this->config);
        } else {
            $handler = new NotFoundHandler($this->config);
        }
        $handler->handle();
    }
}
