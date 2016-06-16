<?php

require_once __DIR__ . '/vendor/autoload.php';

use \Symfony\Component\Yaml\Yaml;
use \Symfony\Component\Yaml\Exception\ParseException;

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
*/

// This has to be a directory relative to where this script is and served by Apache
$spkDir = 'packages/';

// File where Syno models are stored in Yaml format
$synologyModels = 'conf/synology_models.yaml';
$excludedSynoServices = array('apache-sys', 'apache-web', 'mdns', 'samba', 'db', 'applenetwork', 'cron', 'nfs', 'firewall');
$host = $_SERVER['HTTP_HOST'] . substr($_SERVER['REQUEST_URI'], 0, strrpos($_SERVER['REQUEST_URI'], '/')) . '/';

$siteName = 'Simple SPK Server';

if (isset($_REQUEST['ds_sn'])) {
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
    $packageList = displayPackagesJSON(getPackageList($host, $spkDir, $arch, $channel, $fw_version), $excludedSynoServices);
    echo stripslashes(json_encode($packageList));
} elseif ($_SERVER['REQUEST_METHOD'] == 'GET') {
    // GET-request, probably browser --> show HTML
    $arch     = trim($_GET['arch']);
    $channel  = trim($_GET['channel']);
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
        'channel'    => $channel,
        'requestUri' => $_SERVER['REQUEST_URI'],
        'host'       => $host,
        'fullList'   => $fullList,
    );

    if ($arch) {
        // Architecture is set --> show packages for that arch
        $packages = getPackageList($host, $spkDir, $arch, $channel, 'skip');
        $tpl_vars['packagelist'] = array_values($packages);
        $tpl = $mustache->loadTemplate('html_packagelist');
    } elseif ($fullList) {
        // No architecture, but full list of packages requested --> show simple list
        $packages = getAllPackages($spkDir, $host);
        $tpl_vars['packagelist'] = $packages;
        $tpl = $mustache->loadTemplate('html_packagelist_all');
    } else {
        // Nothing requested --> show models overview
        $models = getSynoModels($synologyModels);
        if (is_subclass_of($models, 'RuntimeException')) {
            $tpl_vars['errorMessage'] = $models->getMessage();
            $tpl = $mustache->loadTemplate('html_modellist_error');
        } elseif (count($models) == 0) {
            $tpl = $mustache->loadTemplate('html_modellist_none');
        } else {
            $tpl_vars['modellist'] = $models;
            $tpl = $mustache->loadTemplate('html_modellist');
        }
    }
    echo $tpl->render($tpl_vars);
} else {
    header('Content-type: text/html');
    header('HTTP/1.1 404 Not Found');
    header('Status: 404 Not Found');
}

/**
 * Returns a list of thumbnails for the specified package.
 *
 * @param string $spkDir Package directory
 * @param string $baseFile Package file name without extension
 * @param string $host Hostname
 * @return array List of thumbnails
 */
function getThumbnails($spkDir, $baseFile, $host)
{
    $thumbnails = array();
    foreach (array('72', '120') as $size) {
        $thumb_name = $baseFile . '_thumb_' . $size . '.png';
        // Use $size px thumbnail, if available
        if (file_exists($spkDir . $thumb_name)) {
            $thumbnails[] = 'http://' . $host . $spkDir . $thumb_name;
        } else {
            $thumbnails[] = 'http://' . $host . $spkDir . 'default_package_icon_' . $size . '.png';
        }
    }
    return $thumbnails;
}

/**
 * Returns a list of screenshots for the specified package.
 *
 * @param string $spkDir Package directory
 * @param string $baseFile Package file name without extension
 * @param string $host Hostname
 * @return array List of screenshots
 */
function getSnapshots($spkDir, $baseFile, $host)
{
    $snapshots = array();
    // Add screenshots, if available
    foreach (glob($spkDir . $baseFile . '*_screen_*.png') as $snapshot) {
        $snapshots[] = 'http://' . $host . $snapshot;
    }
    return $snapshots;
}

/**
 * Returns if the given package is eligible for the specified target.
 *
 * @param array $packageInfo Package information
 * @param array $allPackages All previously discovered packages
 * @param string $arch Architecture (or 'noarch')
 * @param string $fw_version Target firmware version (or 'skip')
 * @param string $beta Beta version requested ('beta') or not ('')
 * @return bool
 */
function isPackageEligible($packageInfo, $allPackages, $arch, $fw_version, $beta)
{
    $pkgName    = $packageInfo['package'];
    $pkgVersion = $packageInfo['version'];
    $pkgArch    = $packageInfo['arch'];

    if (isset($allPackages[$pkgName]) && version_compare($allPackages[$pkgName]['version'], $pkgVersion, '>=')) {
        // Package already found and newer or same than this one
        return false;
    }
    if (!in_array($arch, $pkgArch) && !in_array('noarch', $pkgArch)) {
        // Package isn't for this architecture (and not generic)
        return false;
    }
    if (isset($packageInfo['beta']) && ($packageInfo['beta'] == true) && ($beta != 'beta')) {
        // Package is beta version, but beta not requested
        return false;
    }
    if (version_compare($packageInfo['firmware'], $fw_version, '>') && ($fw_version != 'skip')) {
        // Package needs later firmware and check isn't skipped
        return false;
    }
    // All checks passed.
    return true;
}

/**
 * Returns the list of available packages incl. metadata.
 *
 * @param string $arch Requested architecture
 * @param mixed $beta Either 'beta' to also get beta packages, or false
 * @param string $version Firmware version to support
 * @return array
 */
function getPackageList($host, $spkDir, $arch = 'noarch', $beta = false, $version = '')
{
    $packagesList = glob($spkDir . '*.nfo');
    $packagesAvailable = array();
    foreach ($packagesList as $nfoFile) {
        $nfoFile     = basename($nfoFile);
        $baseFile    = basename($nfoFile, '.nfo');
        $spkFile     = $baseFile . '.spk';
        if (!file_exists($spkDir . $spkFile)) {
            continue;
        }
        $packageInfo = parse_ini_file($spkDir . $nfoFile);
        $packageInfo['nfo'] = $spkDir . $nfoFile;
        $packageInfo['spk'] = $spkDir . $spkFile;
        $packageInfo['spk_url'] = 'http://' . $host . $spkDir . $spkFile;
        $packageInfo['thumbnail'] = getThumbnails($spkDir, $baseFile, $host);
        $packageInfo['snapshot'] = getSnapshots($spkDir, $baseFile, $host);

        // Convert architecture(s) to array, as multiple architectures can be specified
        $packageInfo['arch'] = explode(' ', $packageInfo['arch']);
        if (isPackageEligible($packageInfo, $packagesAvailable, $arch, $version, $beta)) {
            $packagesAvailable[$packageInfo['package']] = $packageInfo;
        }
    }
    return $packagesAvailable;
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
    $packagesJSON = array();
    foreach ($packagesAvailable as $packageInfo) {
        $packageJSON = array(
            'package'   => $packageInfo['package'],
            'version'   => $packageInfo['version'],
            'dname'     => $packageInfo['displayname'],
            'desc'      => $packageInfo['description'],
            'link'      => $packageInfo['spk_url'],
            'md5'       => md5_file($packageInfo['spk']),
            'thumbnail' => $packageInfo['thumbnail'],                    // New property for newer synos, need to check if it works with old synos
            'snapshot'  => ifempty($packageInfo, 'snapshot'),            // Adds multiple screenshots to package view
            'size'      => filesize($packageInfo['spk']),
            'qinst'     => ifempty($packageInfo, 'qinst', false),        // quick install
            'qstart'    => ifempty($packageInfo, 'start', false),        // quick start
            'depsers'   => ifempty($packageInfo, 'start_dep_services'),  // required started packages
            'deppkgs'   => !empty($packageInfo['install_dep_services'])?trim(str_replace($excludedSynoServices, '', $packageInfo['install_dep_services'])):null,
            'conflictpkgs' => null,
            'start'     => true,
            //'maintainer'      => $packageInfo['maintainer'],
            //'maintainer_url'  => 'http://dummy.org/',
            //'distributor'     => $packageInfo['maintainer'],
            //'distributor_url' => 'http://dummy.org/',
            'changelog' => ifempty($packageInfo, 'changelog', ''),
            'developer' => null,
            //'support_url' => 'http://dummy.org/',
            'beta'      => ifempty($packageInfo, 'beta', false),         // beta channel
            'thirdparty' => true,
            'model'     => null,
            //'icon'      => $packageInfo['thumbnail'][0],               // Old icon property for pre 4.2 compatibility
            //'icon'      => $packageInfo['package_icon'],               // Get icon from INFO file
            //'category'  => 2,                                          // New property introduced, no effect on othersources packages
            'download_count' => 6000,                                    // Will only display values over 1000
            //'price'     => 0,                                          // New property
            'recent_download_count' => 1222,                             // Not sure what this does
            //'type'      => 0                                           // New property introduced, no effect on othersources packages
        );
        $packagesJSON[] = $packageJSON;
    }

    // Add GPG key, if it exists
    if (file_exists('./gpgkey.asc')) {
        $mygpgkey     = file_get_contents('./gpgkey.asc');
        $mygpgkey     = str_replace("\n", "\\n", $mygpgkey);
        $keyring      = array(0 => $mygpgkey);
        $packagesJSON = array('keyrings' => $keyring, 'packages' => $packagesJSON);  // Add GPG key in [keyrings], and packages as [packages]
    }
    return $packagesJSON;
}

function getAllPackages($spkDir, $host)
{
    $packages = array();
    $packagesList = glob($spkDir . '*.spk');
    foreach ($packagesList as $spkFile) {
        $packages[] = array(
            'url'      => 'http://' . $host . $spkFile,
            'filename' => basename($spkFile),
        );
    }
    return $packages;
}

function getSynoModels($synologyModelsFile)
{
    $models = array();
    if (file_exists($synologyModelsFile)) {
        try {
            /** @var array $archlist */
            $archlist = Yaml::parse(file_get_contents('conf/synology_models.yaml'));
        } catch (ParseException $e) {
            return $e;
        }
        $idx = 0;
        foreach ($archlist as $arch => $archmodels) {
            foreach ($archmodels as $model) {
                $models[$idx] = array(
                    'arch' => $arch,
                    'name' => $model,
                );
                $sortkey[$idx] = $model;
                $idx++;
            }
        }
        array_multisort($sortkey, SORT_NATURAL, $models);
    }
    return $models;
}
