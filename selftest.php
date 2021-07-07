<!DOCTYPE html>
<html>
<head>
    <title>SSPkS Selftest</title>
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <link rel="stylesheet" href="data/css/style.css" type="text/css" />
    <link rel="stylesheet" href="data/css/style_mobile.css" type="text/css" media="handheld"/>
    <!-- Colours from: https://www.materialui.co/colors -->
    <style type="text/css">
        .check {
            width: 80%;
            background-color: #cfd8dc;
            vertical-align: middle;
        }

        .check .checkline {
            height: 3em;
            position: relative;
        }

        .check .description {
            height: 3em;
            line-height: 3em;
            margin-left: 2em;
        }

        .check .description span {
            display: inline-block;
            line-height: 1em;
            vertical-align: middle;
            font-weight: bold;
        }

        .checkline .result {
            position: absolute;
            right: 0px;
            bottom: 0px;
            height: 3em;
            line-height: 3em;
            width: 3em;
            text-align: center;
        }

        .checkline .result span {
            display: inline-block;
            line-height: 1em;
            vertical-align: middle;
            font-size: 2.5em;
        }

        .ok {
            background-color: #4caf50;
            color: white;
        }

        .error {
            background-color: #d50000;
            color: white;
        }

        .errortext {
            margin-left: 1em;
            padding-left: 1em;
            margin-right: 4em;
            margin-top: 0.3em;
            padding-top: 0.3em;
            padding-bottom: 0.3em;
            background-color: #ffcdd2;
        }
    </style>
</head>
<body>
<h1>SSPkS Selftest</h1>
<p>
<?php

function assertTrue($assertion, $description, $error_text)
{
    echo('<div class="check">');
    echo('<div class="checkline">');
    echo('<div class="description"><span>' . $description . '</span></div>');
    if ($assertion === true) {
        // All OK
        echo('<div class="result ok"><span>✔</span></div>');
        echo('</div>'); // close checkline
    } else {
        // Not OK
        echo('<div class="result error"><span>✖</span></div>');
        echo('</div>'); // close checkline
        echo('<div class="errortext">' . $error_text . '</div>');
    }
    echo('</div>');
    echo('<br/>');
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
    is_dir(dirname(__FILE__) . '/vendor'),
    'Composer <tt>vendor</tt> directory exists',
    'Please download and run Composer according to the <tt>INSTALL.md</tt>.'
);

assertTrue(
    file_exists(dirname(__FILE__) . '/vendor/autoload.php'),
    'Composer <tt>vendor/autoload.php</tt> was generated',
    'Please download and run Composer according to the <tt>INSTALL.md</tt>.'
);

assertTrue(
    is_dir(sys_get_temp_dir()),
    'System temporary directory (' . sys_get_temp_dir() . ') exists.',
    'Make sure your temporary directory exists and is writeable and your environment variables (<tt>TMP</tt>, <tt>TEMP</tt>) are set or set <tt>sys_temp_dir</tt> in your <tt>php.ini</tt>.'
);

assertTrue(
    is_writeable(sys_get_temp_dir()),
    'System temporary directory (' . sys_get_temp_dir() . ') is writeable.',
    'Make sure your temporary directory is writeable for the web server process.'
);

// NOTE: (From PHP doc:) A boolean ini value of _off_ will be returned as an empty
//       string or "0" while a boolean ini value of _on_ will be returned as "1".
//       The function can also return the literal string of INI value.
assertTrue(
    (boolval(ini_get('allow_url_fopen')) === true),
    'Using URLs in <tt>fopen()</tt> is allowed',
    'Please set <tt>allow_url_fopen</tt> to <tt>true</tt> in your <tt>php.ini</tt>.'
);

assertTrue(
    is_writable(dirname(__FILE__) . '/cache/'),
    'Directory <tt>cache/</tt> writeable',
    'Please make the <tt>cache/</tt> directory writeable for the web server process.'
);

$test_file = dirname(__FILE__) . '/cache/testfile.$$$';

assertTrue(
    (file_put_contents($test_file, 'TestData12345678') === 16),
    'Can write testfile to <tt>cache/</tt> directory',
    'Please make the <tt>cache/</tt> directory writeable for the web server process.'
);

assertTrue(
    unlink($test_file),
    'Can remove testfile from <tt>cache/</tt> directory',
    'Please make the <tt>cache/</tt> directory writeable for the web server process (also allow deletions).'
);

/*
assertTrue(
    false,
    'This is to see how a failed test looks like.',
    'Don\'t panic. This is only here during development.'
);
*/

?>
</body>
</html>
