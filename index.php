<?php

if (!file_exists(__DIR__ . '/vendor/autoload.php')) {
    print('Autoloader not found! Did you follow the instructions from the INSTALL.md?<br />');
    print('(If you want to keep the old version, switch to the <tt>legacy</tt> branch by running: <tt>git checkout legacy</tt>');
    exit();
}

require_once __DIR__ . '/vendor/autoload.php';

use \SSpkS\Config;
use \SSpkS\Device\DeviceList;
use \SSpkS\Output\JsonOutput;
use \SSpkS\Output\UrlFixer;
use \SSpkS\Package\Package;
use \SSpkS\Package\PackageFinder;
use \SSpkS\Package\PackageFilter;

$config = new Config(__DIR__, 'conf/sspks.yaml');

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

// This has to be a directory relative to where this script is and served by Apache
$spkDir = $config->paths['packages'];

// File where Syno models are stored in Yaml format
$synologyModels = $config->paths['models'];
$excludedSynoServices = $config->excludedSynoServices;
$baseUrl = 'http' . ($_SERVER['HTTPS']?'s':'') . '://' . $_SERVER['HTTP_HOST'] . substr($_SERVER['REQUEST_URI'], 0, strrpos($_SERVER['REQUEST_URI'], '/')) . '/';

$siteName = $config->site['name'];

if (isset($_REQUEST['unique']) && substr($_REQUEST['unique'], 0, 8) == 'synology') {
    // Synology request --> show JSON
    $language = trim($_REQUEST['language']);
    $timezone = trim($_REQUEST['timezone']);
    $arch     = trim($_REQUEST['arch']);
    $major    = trim($_REQUEST['major']);
    $minor    = trim($_REQUEST['minor']);
    $build    = trim($_REQUEST['build']);
    $channel  = trim($_REQUEST['package_update_channel']);
    $unique   = trim($_REQUEST['unique']);

    if ($arch == '88f6282') {
        $arch = '88f6281';
    }

    // Make sure, that the "client" knows that output is sent in JSON format
    header('Content-type: application/json');
    $fw_version = $major . '.' . $minor . '.' . $build;
    $pkgs = new PackageFinder($spkDir);
    $pkgf = new PackageFilter($pkgs->getAllPackages());
    $pkgf->setArchitectureFilter($arch);
    $pkgf->setChannelFilter($channel);
    $pkgf->setFirmwareVersionFilter($fw_version);
    $pkgf->setOldVersionFilter(true);
    $filteredPkgList = $pkgf->getFilteredPackageList();

    $uf = new UrlFixer($baseUrl);
    $uf->fixPackageList($filteredPkgList);

    $jo = new JsonOutput();
    $jo->setExcludedServices($excludedSynoServices);
    $jo->outputPackages($filteredPkgList);
} elseif ($_SERVER['REQUEST_METHOD'] == 'GET') {
    // GET-request, probably browser --> show HTML
    $arch     = trim($_GET['arch']);
    $channel  = trim($_GET['channel']);
    if ($channel != 'beta') {
        $channel = 'stable';
    }
    $fullList = trim($_GET['fulllist']);
    $packagesAvailable = array();

    $mustache = new Mustache_Engine(array(
        'loader'          => new Mustache_Loader_FilesystemLoader(dirname(__FILE__) . '/data/templates'),
        'partials_loader' => new Mustache_Loader_FilesystemLoader(dirname(__FILE__) . '/data/templates/partials'),
        'charset'         => 'utf-8',
        'logger'          => new Mustache_Logger_StreamLogger('php://stderr'),
    ));

    $tpl_vars = array(
        'siteName'   => $siteName,
        'arch'       => $arch,
        'channel'    => ($channel == 'beta'),
        'requestUri' => $_SERVER['REQUEST_URI'],
        'baseUrl'    => $baseUrl,
        'fullList'   => $fullList,
    );

    if ($arch) {
        // Architecture is set --> show packages for that arch
        $pkgs = new PackageFinder($spkDir);
        $pkgf = new PackageFilter($pkgs->getAllPackages());
        $pkgf->setArchitectureFilter($arch);
        $pkgf->setChannelFilter($channel);
        $pkgf->setFirmwareVersionFilter(false);
        $pkgf->setOldVersionFilter(true);
        $filteredPkgList = $pkgf->getFilteredPackageList();

        $uf = new UrlFixer($baseUrl);
        $uf->fixPackageList($filteredPkgList);

        $packages = array();
        foreach ($filteredPkgList as $pkg) {
            $packages[] = $pkg->getMetadata();
        }

        $tpl_vars['packagelist'] = array_values($packages);
        $tpl = $mustache->loadTemplate('html_packagelist');
    } elseif ($fullList) {
        // No architecture, but full list of packages requested --> show simple list
        $pkgs = new PackageFinder($spkDir);
        $packagesList = $pkgs->getAllPackageFiles();

        // Prepare data for template
        $packages = array();
        foreach ($packagesList as $spkFile) {
            $packages[] = array(
                'url'      => $baseUrl . $spkFile,
                'filename' => basename($spkFile),
            );
        }
        $tpl_vars['packagelist'] = $packages;
        $tpl = $mustache->loadTemplate('html_packagelist_all');
    } else {
        // Nothing requested --> show models overview
        try {
            $deviceList = new DeviceList($synologyModels);
            $models = $deviceList->getDevices();
            if (count($models) == 0) {
                $tpl = $mustache->loadTemplate('html_modellist_none');
            } else {
                $tpl_vars['modellist'] = $models;
                $tpl = $mustache->loadTemplate('html_modellist');
            }
        } catch (\Exception $e) {
            $tpl_vars['errorMessage'] = $e->getMessage();
            $tpl = $mustache->loadTemplate('html_modellist_error');
        }
    }
    echo $tpl->render($tpl_vars);
} else {
    header('Content-type: text/html');
    header('HTTP/1.1 404 Not Found');
    header('Status: 404 Not Found');
}
