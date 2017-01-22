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
    if ($assertion) {
        // All OK
        echo('<div class="result ok"><span>✔<br/>OK</span></div>');
        echo('</div>');  // close checkline
    } else {
        // Not OK
        echo('<div class="result error"><span>✖<br/>ERR</span></div>');
        echo('</div>');  // close checkline
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
    is_writable('packages/'),
    'Directory <tt>packages/</tt> writeable',
    'Please make the <tt>packages/</tt> directory writeable for the web server process.'
);

assertTrue(
    (ini_get('allow_url_fopen') == true),
    'Using URLs in <tt>fopen()</tt> is allowed',
    'Please set <tt>allow_url_fopen</tt> to <tt>true</tt> in your <tt>php.ini</tt>.'
);

assertTrue(
    false,
    'This is to see how a failed test looks like.',
    'Don\'t panic. This is only here during development.'
);

// TEST: Probably write a test file to packages/ dir and delete again

?>
</body>
</html>
