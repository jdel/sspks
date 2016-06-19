<?php

namespace SSpkS\Output;

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
        if (property_exists($obj, $property) && !empty($obj->$property)) {
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
        $packageJSON = array(
            'package'   => $pkg->package,
            'version'   => $pkg->version,
            'dname'     => $pkg->displayname,
            'desc'      => $pkg->description,
            'link'      => $pkg->spk_url,
            'md5'       => md5_file($pkg->spk),
            'thumbnail' => $pkg->thumbnail_url,       // New property for newer synos, need to check if it works with old synos
            'snapshot'  => $pkg->snapshot_url,        // Adds multiple screenshots to package view
            'size'      => filesize($pkg->spk),
            'qinst'     => $this->ifEmpty($pkg, 'qinst', false),        // quick install
            'qstart'    => $this->ifEmpty($pkg, 'start', false),        // quick start
            'qupgrade'  => $this->ifEmpty($pkg, 'qupgrade', false),     // quick upgrade
            'depsers'   => $this->ifEmpty($pkg, 'start_dep_services'),  // required started packages
            'deppkgs'   => !empty($pkg->install_dep_services)?trim(str_replace($this->excludedServices, '', $pkg->install_dep_services)):null,
            'conflictpkgs' => null,
            'start'      => true,
            'maintainer'      => $this->ifEmpty($pkg, 'maintainer', 'SSpkS'),
            'maintainer_url'  => $this->ifEmpty($pkg, 'maintainer_url', 'http://dummy.org/'),
            'distributor'     => $this->ifEmpty($pkg, 'distributor', 'SSpkS'),
            'distributor_url' => $this->ifEmpty($pkg, 'distributor_url', 'http://dummy.org/'),
            'changelog'  => $this->ifEmpty($pkg, 'changelog', ''),
            'developer'  => null,
            //'support_url' => 'http://dummy.org/',
            'beta'       => $pkg->beta,         // beta channel
            'thirdparty' => true,
            'model'      => null,
            //'icon'       => $pkg->thumbnail[0],               // Old icon property for pre 4.2 compatibility
            //'icon'       => $pkg->package_icon,               // Get icon from INFO file
            //'category'   => 2,                                          // New property introduced, no effect on othersources packages
            'download_count' => 6000,                                    // Will only display values over 1000
            //'price'      => 0,                                          // New property
            'recent_download_count' => 1222,                             // Not sure what this does
            //'type'       => 0                                           // New property introduced, no effect on othersources packages
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
            $jsonOutput['keyrings'] = $keyring;  // Add GPG key in [keyrings], and packages as [packages]
        }

        echo stripslashes(json_encode($jsonOutput));
    }
}
