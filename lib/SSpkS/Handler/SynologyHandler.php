<?php

namespace SSpkS\Handler;

use \SSpkS\Output\JsonOutput;
use \SSpkS\Output\UrlFixer;
use \SSpkS\Package\PackageFinder;
use \SSpkS\Package\PackageFilter;

/*
example data passed by a syno

language = enu
timezone = Brussels
unique = synology_cedarview_412
arch = cedarview
major = 4
minor = 1
build = 2636
package_update_channel = stable

    [package_update_channel] => beta
    [unique] => synology_avoton_415+
    [build] => 7393
    [language] => enu
    [major] => 6
    [arch] => avoton
    [minor] => 0
    [timezone] => Amsterdam
*/

class SynologyHandler extends AbstractHandler
{
    public function canHandle()
    {
        return (isset($_REQUEST['unique']) && substr($_REQUEST['unique'], 0, 8) == 'synology');
    }

    public function handle()
    {
        // Synology request --> show JSON
        $arch     = trim($_REQUEST['arch']);
        $major    = trim($_REQUEST['major']);
        $minor    = trim($_REQUEST['minor']);
        $build    = trim($_REQUEST['build']);
        $channel  = trim($_REQUEST['package_update_channel']);
        // more parameters: language, timezone and unique

        if ($arch == '88f6282') {
            $arch = '88f6281';
        }

        // Make sure, that the "client" knows that output is sent in JSON format
        header('Content-type: application/json');
        $fw_version = $major . '.' . $minor . '.' . $build;
        $pkgs = new PackageFinder($this->config, $this->config->paths['packages']);
        $pkgf = new PackageFilter($pkgs->getAllPackages());
        $pkgf->setArchitectureFilter($arch);
        $pkgf->setChannelFilter($channel);
        $pkgf->setFirmwareVersionFilter($fw_version);
        $pkgf->setOldVersionFilter(true);
        $filteredPkgList = $pkgf->getFilteredPackageList();

        $uf = new UrlFixer($this->config->baseUrl);
        $uf->fixPackageList($filteredPkgList);

        $jo = new JsonOutput($this->config);
        $jo->setExcludedServices($this->config->excludedSynoServices);
        $jo->outputPackages($filteredPkgList);
    }
}
