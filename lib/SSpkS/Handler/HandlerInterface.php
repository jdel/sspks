<?php

namespace SSpkS\Handler;

interface HandlerInterface
{
    public function __construct(\SSpkS\Config $config);

    public function handle();
}
