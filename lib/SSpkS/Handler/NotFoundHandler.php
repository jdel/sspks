<?php

namespace SSpkS\Handler;

class NotFoundHandler implements HandlerInterface
{

    public function __construct(\SSpkS\Config $config)
    {
    }

    public function handle()
    {
        header('Content-type: text/html');
        header('HTTP/1.1 404 Not Found');
        header('Status: 404 Not Found');
    }
}
