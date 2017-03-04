<?php

namespace SSpkS\Handler;

abstract class AbstractHandler
{
    protected $config;

    public function __construct(\SSpkS\Config $config)
    {
        $this->config = $config;
    }

    abstract public function canHandle();
    abstract public function handle();
}
