<?php

namespace SSpkS\Handler;

class BrowserRedirectHandler extends AbstractHandler
{
    public function canHandle()
    {
        return (isset($this->config->site['redirectindex']) && !empty($this->config->site['redirectindex']));
    }

    public function handle()
    {
        header('Location: ' . $this->config->site['redirectindex']);
    }
}
