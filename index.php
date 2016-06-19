<?php

require_once __DIR__ . '/vendor/autoload.php';

use \SSpkS\Device\DeviceList;
use \SSpkS\Output\UrlFixer;
use \SSpkS\Package\Package;
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

// This has to be a directory relative to where this script is and served by Apache
$spkDir = 'packages/';

// File where Syno models are stored in Yaml format
$synologyModels = 'conf/synology_models.yaml';
$excludedSynoServices = array('apache-sys', 'apache-web', 'mdns', 'samba', 'db', 'applenetwork', 'cron', 'nfs', 'firewall');
$baseUrl = 'http' . ($_SERVER['HTTPS']?'s':'') . '://' . $_SERVER['HTTP_HOST'] . substr($_SERVER['REQUEST_URI'], 0, strrpos($_SERVER['REQUEST_URI'], '/')) . '/';

$siteName = 'Simple SPK Server';

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

    $packages = array();
    foreach ($filteredPkgList as $pkg) {
        $packages[] = $pkg->getMetadata();
    }

    $packageList = displayPackagesJSON($packages, $excludedSynoServices);
    $result = stripslashes(json_encode($packageList));
    echo $result;
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

/**
 * Checks if $array contains $key and if not, returns $alternative.
 *
 * @param array $array Array to check
 * @param string $key Key to check for
 * @param mixed $alternative Alternative to return if key not found
 * @return mixed Value from $array[$key] or $alternative
 */
function ifempty($array, $key, $alternative = null)
{
    if (!empty($array[$key])) {
        return $array[$key];
    }
    return $alternative;
}

function displayPackagesJSON($packagesAvailable, $excludedSynoServices = array())
{
    // Format: https://github.com/piwi82/Synology/wiki/Package-catalog
    $packagesJSON = array('packages' => array());
    foreach ($packagesAvailable as $packageInfo) {
        $packageJSON = array(
            'package'   => $packageInfo['package'],
            'version'   => $packageInfo['version'],
            'dname'     => $packageInfo['displayname'],
            'desc'      => $packageInfo['description'],
            'link'      => $packageInfo['spk_url'],
            'md5'       => md5_file($packageInfo['spk']),
            'thumbnail' => $packageInfo['thumbnail_url'],                // New property for newer synos, need to check if it works with old synos
            'snapshot'  => ifempty($packageInfo, 'snapshot_url'),        // Adds multiple screenshots to package view
            'size'      => filesize($packageInfo['spk']),
            'qinst'     => ifempty($packageInfo, 'qinst', false),        // quick install
            'qstart'    => ifempty($packageInfo, 'start', false),        // quick start
            'qupgrade'  => ifempty($packageInfo, 'qupgrade', false),     // quick upgrade
            'depsers'   => ifempty($packageInfo, 'start_dep_services'),  // required started packages
            'deppkgs'   => !empty($packageInfo['install_dep_services'])?trim(str_replace($excludedSynoServices, '', $packageInfo['install_dep_services'])):null,
            'conflictpkgs' => null,
            'start'      => true,
            'maintainer'      => ifempty($packageInfo, 'maintainer', 'SSpkS'),
            'maintainer_url'  => ifempty($packageInfo, 'maintainer_url', 'http://dummy.org/'),
            'distributor'     => ifempty($packageInfo, 'distributor', 'SSpkS'),
            'distributor_url' => ifempty($packageInfo, 'distributor_url', 'http://dummy.org/'),
            'changelog'  => ifempty($packageInfo, 'changelog', ''),
            'developer'  => null,
            //'support_url' => 'http://dummy.org/',
            'beta'       => ($packageInfo['beta'] == 'beta'),         // beta channel
            'thirdparty' => true,
            'model'      => null,
            //'icon'       => $packageInfo['thumbnail'][0],               // Old icon property for pre 4.2 compatibility
            //'icon'       => $packageInfo['package_icon'],               // Get icon from INFO file
            //'category'   => 2,                                          // New property introduced, no effect on othersources packages
            'download_count' => 6000,                                    // Will only display values over 1000
            //'price'      => 0,                                          // New property
            'recent_download_count' => 1222,                             // Not sure what this does
            //'type'       => 0                                           // New property introduced, no effect on othersources packages
        );
        $packagesJSON['packages'][] = $packageJSON;
    }

    // Add GPG key, if it exists
    if (file_exists('./gpgkey.asc')) {
        $mygpgkey     = file_get_contents('./gpgkey.asc');
        $mygpgkey     = str_replace("\n", "\\n", $mygpgkey);
        $keyring      = array(0 => $mygpgkey);
        $packagesJSON['keyrings'] = $keyring;  // Add GPG key in [keyrings], and packages as [packages]
    }
    return $packagesJSON;
}
