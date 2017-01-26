<?php

namespace SSpkS\Handler;

use \SSpkS\Output\HtmlOutput;
use \SSpkS\Output\UrlFixer;
use \SSpkS\Package\PackageFilter;
use \SSpkS\Package\PackageFinder;

class BrowserPackageListHandler extends AbstractHandler
{
    public function handle()
    {
        // Architecture is set --> show packages for that arch
        $arch     = trim($_GET['arch']);
        $channel  = trim($_GET['channel']);
        if ($channel != 'beta') {
            $channel = 'stable';
        }

        $output = new HtmlOutput($this->config);
        $output->setVariable('arch', $arch);
        $output->setVariable('channel', ($channel == 'beta'));

        $pkgs = new PackageFinder($this->config->paths['packages']);
        $pkgf = new PackageFilter($pkgs->getAllPackages());
        $pkgf->setArchitectureFilter($arch);
        $pkgf->setChannelFilter($channel);
        $pkgf->setFirmwareVersionFilter(false);
        $pkgf->setOldVersionFilter(true);
        $filteredPkgList = $pkgf->getFilteredPackageList();

        $uf = new UrlFixer($this->config->baseUrl);
        $uf->fixPackageList($filteredPkgList);

        $packages = array();
        foreach ($filteredPkgList as $pkg) {
            $packages[] = $pkg->getMetadata();
        }

        $output->setVariable('packagelist', array_values($packages));
        $output->setTemplate('html_packagelist');
        $output->output();
    }
}
