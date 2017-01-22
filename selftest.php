<!DOCTYPE html>
<html>
<head>
    <title>SSPkS Selftest</title>
</head>
<body>
<h1>SSPkS Selftest</h1>
<p>
<?php

function assertTrue($assertion, $description, $error_text)
{
    print('Test: ' . $description . ': ');
    if ($assertion) {
        // All OK
        print('OK');
    } else {
        // Not OK
        print($error_text);
    }
    print('<br/>');
}


assertTrue(
    version_compare(phpversion(), '5.6', '>='),
    'PHP Version (' . phpversion() . ')',
    'Please use a more recent PHP version for optimal performance. PHP 7 preferred.'
);

assertTrue(
    extension_loaded('phar'),
    'Phar extension installed',
    'Please install/enable the <tt>php-phar</tt> extension.'
);

assertTrue(
    is_writable('packages/'),
    'Directory <tt>packages/</tt> writeable',
    'Please make the <tt>packages/</tt> directory writeable for the web server process.'
);

assertTrue(
    (ini_get('allow_url_fopen') == true),
    'Using URLs in <tt>fopen()</tt> is allowed',
    'Please set <tt>allow_url_fopen</tt> to <tt>true</tt> in your <tt>php.ini</tt>.'
);

// TEST: Probably write a test file to packages/ dir and delete again

?>
</body>
</html>
