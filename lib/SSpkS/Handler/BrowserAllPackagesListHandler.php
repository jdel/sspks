<?php

namespace SSpkS\Handler;

use \SSpkS\Output\HtmlOutput;
use \SSpkS\Package\PackageFinder;

class BrowserAllPackagesListHandler extends AbstractHandler
{
    public function handle()
    {
        // No architecture, but full list of packages requested --> show simple list
        $output = new HtmlOutput($this->config);

        $pkgs = new PackageFinder($this->config->paths['packages']);
        $packagesList = $pkgs->getAllPackageFiles();

        // Prepare data for template
        $packages = array();
        foreach ($packagesList as $spkFile) {
            $packages[] = array(
                'url'      => $this->config->baseUrl . $spkFile,
                'filename' => basename($spkFile),
            );
        }
        $output->setVariable('packagelist', $packages);
        $output->setTemplate('html_packagelist_all');
        $output->output();
    }
}