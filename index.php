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
    $packageList = DisplayPackagesJSON(getPackageList($host, $spkDir, $arch, $channel, $fw_version), $excludedSynoServices);
    echo stripslashes(json_encode($packageList));
} elseif ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $arch     = trim($_GET['arch']);
    $channel  = trim($_GET['channel']);
    $fullList = trim($_GET['fulllist']);
    $packagesAvailable = array();

    echo "<!DOCTYPE html>\n";
    echo "<html>\n";
    echo "\t<head>\n";
    echo "\t\t<title>" . $siteName . "</title>\n";
    echo "\t\t<meta http-equiv=\"content-type\" content=\"text/html; charset=utf-8\" />\n";
    echo "\t\t<script src=\"vendor/bower-asset/prototypejs-bower/prototype.js\" type=\"text/javascript\"></script>\n";
    echo "\t\t<script src=\"vendor/bower-asset/scriptaculous-bower/scriptaculous.js\" type=\"text/javascript\"></script>\n";
    echo "\t\t<link rel=\"stylesheet\" href=\"data/css/style.css\" type=\"text/css\" />\n";
    echo "\t\t<link rel=\"stylesheet\" href=\"data/css/style_mobile.css\" type=\"text/css\" media=\"handheld\"/>\n";
    echo "\t</head>\n";
    echo "\t<body>\n";
    echo "\t\t<h1>" . $siteName . "</h1>\n";
    echo "\t\t<div id=\"menu\">\n";
    echo "\t\t\t<ul>\n";
    echo "\t\t\t\t<li><a href=\".\">Synology Models</a></li>\n";
    echo ($arch && !$channel)?"\t\t\t\t<li><a href=\"" . $_SERVER['REQUEST_URI'] . "&channel=beta\">Show Beta Packages</a></li>\n":'';
    echo $channel?"\t\t\t\t<li><a href=\"./?arch=" . $arch . "\">Hide Beta Packages</a></li>\n":'';
    echo !$fullList?"\t\t\t\t<li><a href=\"./?fulllist=true\">Full Packages List</a></li>\n":'';
    echo "\t\t\t\t<li class=\"last\"><a href=\"http://github.com/mbirth/sspks\">Host your own packages</a></li>\n";
    echo "\t\t\t</ul>\n";
    echo "\t\t</div>\n";
    echo "\t\t<div id=\"source-info\">\n";
    echo "\t\t\t<p>Add <span>http://" . $host . "</span> to your Synology NAS Package Center sources !</p>\n";
    echo "\t\t</div>\n";
    echo "\t\t<div id=\"content\">\n";
    echo "\t\t\t<ul>\n";
    if ($arch) {
        DisplayPackagesHTML(getPackageList($host, $spkDir, $arch, $channel, 'skip'));
    } elseif ($fullList) {
        DisplayAllPackages($spkDir, $host);
    } else {
        DisplaySynoModels($synologyModels);
    }
    echo "\t\t\t</ul>\n";
    echo "\t\t</div>\n";
    echo "\t\t<hr />\n";
    echo "\t\t<div id=\"footer\">\n";
    echo "\t\t\t<p>Help this website get better on <a href=\"http://github.com/mbirth/sspks\">Github</a></p>\n";
    echo "\t\t</div>\n";
    echo "\t</body>\n";
    echo '</html>';
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
    foreach (GetDirectoryList($spkDir, $baseFile.'*_screen_*.png') as $snapshot) {
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
    $packagesList = GetDirectoryList($spkDir, '*.nfo');
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

function DisplayPackagesHTML($packagesAvailable)
{
    foreach ($packagesAvailable as $packageInfo) {
        echo "\t\t\t\t<li class=\"package\">\n";
        echo "\t\t\t\t\t<div class=\"spk-icon\">\n";
        echo "\t\t\t\t\t\t<a href=\"" . $packageInfo['spk_url'] . '"><img src="' . $packageInfo['thumbnail'][0] . '" alt="' . $packageInfo['displayname'] . '" />' . ($packageInfo['beta']?'<ins></ins>':'') . "</a>\n";
        echo "\t\t\t\t\t</div>\n";
        echo "\t\t\t\t\t<div class=\"spk-desc\">\n";
        echo "\t\t\t\t\t\t<span class=\"spk-title\">" . $packageInfo['displayname'] . ' v' . $packageInfo['version'] . "</span><br />\n";
        echo "\t\t\t\t\t\t<p class=\"dsm-version\">Minimum DSM verison: " . $packageInfo['firmware'] . "</p>\n";
        echo "\t\t\t\t\t\t<p>" . $packageInfo['description'] . "</p>\n";
        echo ' <a id="' . $packageInfo['package'] . '_show" href="#nogo" onclick="Effect.toggle(\'' . $packageInfo['package'] . "_detail', 'blind', { duration: 0.5 }); Effect.toggle('" . $packageInfo['package'] . "_show', 'appear', { duration: 0.3 }); Effect.toggle('" . $packageInfo['package'] . "_hide', 'appear', { duration: 0.3, delay: 0.5 }); return false;\">More...</a>";
        echo ' <a id="' . $packageInfo['package'] . '_hide" href="#nogo" onclick="Effect.toggle(\'' . $packageInfo['package'] . "_detail', 'blind', { duration: 0.5 }); Effect.toggle('" . $packageInfo['package'] . "_hide', 'appear', { duration: 0.3 }); Effect.toggle('" . $packageInfo['package'] . "_show', 'appear', { duration: 0.3, delay: 0.5 }); return false;\" style=\"display: none;\">Hide</a>\n";
        echo "\t\t\t\t\t\t</p>\n";
        echo "\t\t\t\t\t\t<div style=\"display: none;\" id=\"" . $packageInfo['package'] . "_detail\">\n";
        echo "\t\t\t\t\t\t<table>\n";
        echo "\t\t\t\t\t\t\t<tr><td>Package</td><td>" . $packageInfo['package'] . "</td></tr>\n";
        echo "\t\t\t\t\t\t\t<tr><td>Version</td><td>" . $packageInfo['version'] . "</td></tr>\n";
        echo "\t\t\t\t\t\t\t<tr><td>Display Name</td><td>" . $packageInfo['displayname'] . "</td></tr>\n";
        echo "\t\t\t\t\t\t\t<tr><td>Maintainer</td><td>" . $packageInfo['maintainer'] . "</td></tr>\n";
        echo "\t\t\t\t\t\t\t<tr><td>Arch</td><td>" . implode(', ', $packageInfo['arch']) . "</td></tr>\n";
        echo "\t\t\t\t\t\t\t<tr><td>Firmware</td><td>" . $packageInfo['firmware'] . "</td></tr>\n";
        echo "\t\t\t\t\t\t</table>\n";
        echo "\t\t\t\t\t\t</div>\n";
        echo "\t\t\t\t\t</div>\n";
        echo "\t\t\t\t</li>\n";
    }
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

function DisplayPackagesJSON($packagesAvailable, $excludedSynoServices = array())
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
            'beta'      => ifempty($packageInfo, 'beta', false),                             // beta channel
            'thirdparty' => true,
            'model'     => null,
            //'icon'      => $packageInfo['thumbnail'][0],                                                        // Old icon property for pre 4.2 compatibility
            //'icon'      => $packageInfo['package_icon'],                                                        // Get icon from INFO file
            //'category'  => 2,                                                                                   // New property introduced, no effect on othersources packages
            'download_count' => 6000,                                                                           // Will only display values over 1000
            //'price'     => 0,                                                                                   // New property
            'recent_download_count' => 1222,                                                                    // Not sure what this does
            //'type'      => 0                                                                                    // New property introduced, no effect on othersources packages
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

function DisplayAllPackages($spkDir, $host)
{
    $packagesList = GetDirectoryList($spkDir, '*.spk');
    foreach ($packagesList as $spkFile) {
        echo "\t\t\t\t<li><a href=\"http://" . $host . $spkFile . '">' . basename($spkFile) . "</a></li>\n";
    }
}

function DisplaySynoModels($synologyModelsFile)
{
    if (file_exists($synologyModelsFile)) {
        try {
            /** @var array $archlist */
            $archlist = Yaml::parse(file_get_contents('conf/synology_models.yaml'));
        } catch (ParseException $e) {
            echo "\t\t\t\t<li>Error parsing model list: " . $e->getMessage() . '</li>';
            return;
        }
        $synologyModels = array();
        foreach ($archlist as $arch => $archmodels) {
            foreach ($archmodels as $model) {
                $synologyModels[$model] = $arch;
            }
        }
        ksort($synologyModels);
        foreach ($synologyModels as $synoName => $synoArch) {
            echo "\t\t\t\t<li class=\"syno-model\"><a href=\"?arch=" . $synoArch . '">' . $synoName . "</a></li>\n";
        }
    } else {
        echo "\t\t\t\t<li>Couldn't find Synology models</li>";
    }
}

function GetDirectoryList($directory, $filter)
{
    $filelist = glob($directory.$filter);
    return $filelist;
}
