<?php

if (!file_exists(__DIR__ . '/vendor/autoload.php')) {
    print('Autoloader not found! Did you follow the instructions from the INSTALL.md?<br />');
    print('(If you want to keep the old version, switch to the <tt>legacy</tt> branch by running: <tt>git checkout legacy</tt>');
    exit();
}

require_once __DIR__ . '/vendor/autoload.php';

use \SSpkS\Config;
use \SSpkS\Handler;

$config = new Config(__DIR__, 'conf/sspks.yaml');
$config->baseUrl = 'http' . ($_SERVER['HTTPS']?'s':'') . '://' . $_SERVER['HTTP_HOST'] . substr($_SERVER['REQUEST_URI'], 0, strrpos($_SERVER['REQUEST_URI'], '/')) . '/';

$handler = new Handler($config);
$handler->handle();
