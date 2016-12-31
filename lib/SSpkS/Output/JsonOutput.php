<?php

namespace SSpkS\Output;

/**
 * Outputs Packages in JSON format according to
 * https://github.com/piwi82/Synology/wiki/Package-catalog
 */
class JsonOutput
{
    private $excludedServices = array();

    public function __construct()
    {
    }

    /**
     * Sets services to exclude from dependencies on output.
     *
     * @param array $excludedServices Services to exclude.
     */
    public function setExcludedServices($excludedServices)
    {
        $this->excludedServices = $excludedServices;
    }

    /**
     * Checks if $obj contains $property and if not, returns $alternative.
     *
     * @param object $obj Object to check
     * @param string $property Property to check for
     * @param mixed $alternative Alternative to return if key not found
     * @return mixed Value from $obj->$property or $alternative
     */
    private function ifEmpty($obj, $property, $alternative = null)
    {
        if (isset($obj->$property) && !empty($obj->$property)) {
            return $obj->$property;
        }
        return $alternative;
    }

    /**
     * Returns JSON-ready array of Package $pkg.
     *
     * @param \SSpkS\Package\Package $pkg Package
     * @return array JSON-ready array of $pkg.
     */
    private function packageToJson($pkg)
    {
/*
package
version
dname - displayed name
desc
price - 0
download_count - overall DL count
recent_download_count - DL count of last month?
link - URL
size
md5
thumbnail - array[URL]
thumbnail_retina - array[URL] (optional)
snapshot - array[URL] (optional)
qinst - true/false (optional)
qstart - true/false (optional)
qupgrade - true/false (optional)
depsers - "pgsql" (optional)
deppkgs - Pkg1>Version:Pkg2:Pkg3 (optional)
conflictpkgs - Pkg1<Version:Pkg2:Pkg3<Version (optional)
start - true/false (optional)
maintainer - name
maintainer_url - URL (optional)
distributor - name (optional)
distributor_url - URL (optional)
changelog - HTML
support_url - URL (optional)
thirdparty - true/false (optional)
category - 0-128 (bits, for multiple categories?)
subcategory - 0
type - 0 = normal, 1 = driver?, 2 = service?
silent_install - true/false (optional)
silent_uninstall - true/false (optional)
silent_upgrade - true/false (optional)
conf_deppkgs - array[Package[dsm_max_ver, pkg_min_ver]] (optional)
support_conf_folder - true/false (optional)
auto_upgrade_from - version number (optional)
*/

        if (!empty($pkg->install_dep_services)) {
            $deppkgs = trim(str_replace($this->excludedServices, '', $pkg->install_dep_services));
        } else {
            $deppkgs = null;
        }

        $packageJSON = array(
            'package'      => $pkg->package,
            'version'      => $pkg->version,
            'dname'        => $pkg->displayname,
            'desc'         => $pkg->description,
            'price'        => 0,
            'download_count'        => 6000, // Will only display values over 1000
            'recent_download_count' => 1222,
            'link'         => $pkg->spk_url,
            'size'         => filesize($pkg->spk),
            'md5'          => md5_file($pkg->spk),
            'thumbnail'    => $pkg->thumbnail_url,
            'snapshot'     => $pkg->snapshot_url,
            // quick install/start/upgrade
            'qinst'        => $this->ifEmpty($pkg, 'qinst', false),
            'qstart'       => $this->ifEmpty($pkg, 'start', false),
            'qupgrade'     => $this->ifEmpty($pkg, 'qupgrade', false),
            'depsers'      => $this->ifEmpty($pkg, 'start_dep_services'), // required started packages
            'deppkgs'      => $deppkgs,
            'conflictpkgs' => null,
            'start'        => true,
            'maintainer'      => $this->ifEmpty($pkg, 'maintainer', 'SSpkS'),
            'maintainer_url'  => $this->ifEmpty($pkg, 'maintainer_url', 'http://dummy.org/'),
            'distributor'     => $this->ifEmpty($pkg, 'distributor', 'SSpkS'),
            'distributor_url' => $this->ifEmpty($pkg, 'distributor_url', 'http://dummy.org/'),
            'changelog'    => $this->ifEmpty($pkg, 'changelog', ''),
            'thirdparty'   => true,
            'category'     => 0,
            'subcategory'  => 0,
            'type'         => 0,
            'silent_install'   => $this->ifEmpty($pkg, 'silent_install', false),
            'silent_uninstall' => $this->ifEmpty($pkg, 'silent_uninstall', false),
            'silent_upgrade'   => $this->ifEmpty($pkg, 'silent_upgrade', false),
            'beta'         => $pkg->beta, // beta channel
        );
        return $packageJSON;
    }

    /**
     * Outputs given packages as JSON.
     *
     * @param \SSpkS\Package\Package[] $pkgList List of packages to output.
     */
    public function outputPackages($pkgList)
    {
        $jsonOutput = array(
            'packages' => array(),
        );
        foreach ($pkgList as $pkg) {
            $pkgJson = $this->packageToJson($pkg);
            $jsonOutput['packages'][] = $pkgJson;
        }

        // Add GPG key, if it exists
        if (file_exists('./gpgkey.asc')) {
            $mygpgkey     = file_get_contents('./gpgkey.asc');
            $mygpgkey     = str_replace("\n", "\\n", $mygpgkey);
            $keyring      = array(0 => $mygpgkey);
            $jsonOutput['keyrings'] = $keyring;
        }

        echo stripslashes(json_encode($jsonOutput));
    }
}
