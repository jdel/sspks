<?php

namespace SSpkS\Output;

class UrlFixer
{
    private $urlPrefix;

    /**
     * Prepends given $urlPrefix to all URLs in Package objects.
     *
     * @param string $urlPrefix Prefix to put before all URLs to make them absolute.
     */
    public function __construct($urlPrefix)
    {
        $this->urlPrefix = $urlPrefix;
    }

    /**
     * Prepends given prefix to Package $pkg.
     *
     * @param \SSpkS\Package\Package $pkg Package to work on.
     */
    public function fixPackage($pkg)
    {
        $pkg->spk_url = $this->urlPrefix . $pkg->spk;

        // Make absolute URLs from relative ones
        $thumbnail_url = array();
        foreach ($pkg->thumbnail as $i => $t) {
            $thumbnail_url[$i] = $this->urlPrefix . $t;
        }
        $pkg->thumbnail_url = $thumbnail_url;

        $snapshot_url = array();
        foreach ($pkg->snapshot as $i => $s) {
            $snapshot_url[$i] = $this->urlPrefix . $s;
        }
        $pkg->snapshot_url = $snapshot_url;
    }

    /**
     * Prepends given prefix to all Packages in array $pkgList.
     *
     * @param \SSpkS\Package\Package[] List of Packages.
     */
    public function fixPackageList($pkgList)
    {
        foreach ($pkgList as $pkg) {
            $this->fixPackage($pkg);
        }
    }
}
