<?php

namespace SSpkS\Output;

/**
 * Outputs Packages in JSON format according to
 * https://github.com/piwi82/Synology/wiki/Package-catalog
 */
class JsonOutput
{
    private $excludedServices = array();
    private $config;

    public function __construct(\SSpkS\Config $config)
    {
        $this->config = $config;
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
     * @param string $language The output language (this has impact on display name and description)
     * @return array JSON-ready array of $pkg.
     */
    private function packageToJson($pkg, $language)
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
            'dname'        => $this->ifEmpty($pkg, 'displayname_' . $language, $pkg->displayname),
            'desc'         => $this->ifEmpty($pkg, 'description_' . $language, $pkg->description),
            'price'        => 0,
            'download_count'        => 0, // Will only display values over 1000, do not display it by default
            'recent_download_count' => 0,
            'link'         => $pkg->spk_url,
            'size'         => filesize($pkg->spk),
            'md5'          => md5_file($pkg->spk),
            'thumbnail'    => $pkg->thumbnail_url,
            'snapshot'     => $pkg->snapshot_url,
            // quick install/start/upgrade
            'qinst'        => $pkg->qinst,
            'qstart'       => $pkg->qstart,
            'qupgrade'     => $pkg->qupgrade,
            'depsers'      => $this->ifEmpty($pkg, 'start_dep_services'), // required started packages
            'deppkgs'      => $deppkgs,
            'conflictpkgs' => null,
            'start'        => true,
            'maintainer'      => $this->ifEmpty($pkg, 'maintainer', $this->config->packages['maintainer']),
            'maintainer_url'  => $this->ifEmpty($pkg, 'maintainer_url', $this->config->packages['maintainer_url']),
            'distributor'     => $this->ifEmpty($pkg, 'distributor', $this->config->packages['distributor']),
            'distributor_url' => $this->ifEmpty($pkg, 'distributor_url', $this->config->packages['distributor_url']),
            'support_url'  => $this->ifEmpty($pkg, 'support_url', $this->config->packages['support_url']),
            'changelog'    => $this->ifEmpty($pkg, 'changelog', ''),
            'thirdparty'   => true,
            'category'     => 0,
            'subcategory'  => 0,
            'type'         => 0,
            'silent_install'   => $this->ifEmpty($pkg, 'silent_install', false),
            'silent_uninstall' => $this->ifEmpty($pkg, 'silent_uninstall', false),
            'silent_upgrade'   => $this->ifEmpty($pkg, 'silent_upgrade', false),
            'auto_upgrade_from' => $this->ifEmpty($pkg, 'auto_upgrade_from'),
            'beta'         => $pkg->beta, // beta channel
        );
        return $packageJSON;
    }

    /**
     * Outputs given packages as JSON.
     *
     * @param \SSpkS\Package\Package[] $pkgList List of packages to output.
     * @param string $language The output language (this has impact on display name and description)
     */
    public function outputPackages($pkgList, $language = 'enu')
    {
        $jsonOutput = array(
            'packages' => array(),
        );
        foreach ($pkgList as $pkg) {
            $pkgJson = $this->packageToJson($pkg, $language);
            $jsonOutput['packages'][] = $pkgJson;
        }

        // Add GPG key, if it exists
        if (file_exists('./gpgkey.asc')) {
            $mygpgkey     = file_get_contents('./gpgkey.asc');
            $mygpgkey     = str_replace("\n", "\\n", $mygpgkey);
            $keyring      = array(0 => $mygpgkey);
            $jsonOutput['keyrings'] = $keyring;
        }

        echo stripslashes(json_encode($jsonOutput, JSON_UNESCAPED_UNICODE));
    }
}
