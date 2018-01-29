<?php

namespace SSpkS\Handler;

use \SSpkS\Output\HtmlOutput;
use \SSpkS\Output\UrlFixer;
use \SSpkS\Package\PackageFilter;
use \SSpkS\Package\PackageFinder;

class BrowserPackageListHandler extends AbstractHandler
{
    public function canHandle()
    {
        return ($_SERVER['REQUEST_METHOD'] == 'GET' && array_key_exists('arch', $_GET) && !empty(trim($_GET['arch'])));
    }

    public function handle()
    {
        // Architecture is set --> show packages for that arch
        $arch = trim($_GET['arch']);

        $output = new HtmlOutput($this->config);
        $output->setVariable('arch', $arch);

        $pkgs = new PackageFinder($this->config);
        $pkgf = new PackageFilter($this->config, $pkgs->getAllPackages());
        $pkgf->setArchitectureFilter($arch);
        $pkgf->setFirmwareVersionFilter(false);
        $pkgf->setOldVersionFilter(true);
        $filteredPkgList = $pkgf->getFilteredPackageList();

        $uf = new UrlFixer($this->config->baseUrl);
        $uf->fixPackageList($filteredPkgList);

        $packages = array();
        foreach ($filteredPkgList as $pkg) {
            $pkgMeta = $pkg->getMetadata();
            $packages[$pkgMeta['displayname']] = $pkgMeta;
        }

        ksort($packages, SORT_NATURAL | SORT_FLAG_CASE);

        $output->setVariable('packagelist', array_values($packages));
        $output->setTemplate('html_packagelist');
        $output->output();
    }
}
