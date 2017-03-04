<?php

namespace SSpkS\Handler;

class NotFoundHandler extends AbstractHandler
{
    public function canHandle()
    {
        return true;
    }

    public function handle()
    {
        header('Content-type: text/html');
        header('HTTP/1.1 404 Not Found');
        header('Status: 404 Not Found');
    }
}
