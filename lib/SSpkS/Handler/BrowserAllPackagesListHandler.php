<?php

namespace SSpkS\Handler;

use \SSpkS\Output\HtmlOutput;
use \SSpkS\Package\PackageFinder;

class BrowserAllPackagesListHandler extends AbstractHandler
{
    public function canHandle()
    {
        return ($_SERVER['REQUEST_METHOD'] == 'GET' && array_key_exists('fulllist', $_GET) && !empty(trim($_GET['fulllist'])));
    }

    public function handle()
    {
        // No architecture, but full list of packages requested --> show simple list
        $output = new HtmlOutput($this->config);

        $pkgs = new PackageFinder($this->config);
        $packagesList = $pkgs->getAllPackageFiles();

        // Prepare data for template
        $packages = array();
        foreach ($packagesList as $spkFile) {
            $packages[basename($spkFile)] = array(
                'url'      => $this->config->baseUrl . $spkFile,
                'filename' => basename($spkFile),
            );
        }
        ksort($packages, SORT_NATURAL | SORT_FLAG_CASE);
        $output->setVariable('packagelist', array_values($packages));
        $output->setVariable('fullList', true);
        $output->setTemplate('html_packagelist_all');
        $output->output();
    }
}
