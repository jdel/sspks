<?php

namespace SSpkS\Package;

use \SSpkS\Package\Package;

/**
 * SPK PackageFinder class
 */
class PackageFinder
{
    private $config;
    private $fileGlob;
    private $baseFolder;
    private $fileList;

    /**
     * @param \SSpkS\Config $config Config object
     * @param string $folder Folder to search for SPK files
     * @param string $glob Filemask for package files (default: '*.spk')
     * @throws \Exception if $folder is not a folder.
     */
    public function __construct(\SSpkS\Config $config, $folder, $glob = '*.spk')
    {
        $this->config = $config;
        if (!file_exists($folder) || !is_dir($folder)) {
            throw new \Exception($folder . ' is not a folder!');
        }
        if (substr($folder, -1) != '/') {
            $folder .= '/';
        }
        $this->baseFolder = $folder;
        $this->fileGlob   = $glob;
        $this->searchPackageFiles();
    }

    /**
     * Searches the currently set folder with the set glob for package files.
     */
    private function searchPackageFiles()
    {
        $this->fileList = glob($this->baseFolder . $this->fileGlob);
    }

    /**
     * Returns all found package files.
     *
     * @return array List of package files.
     */
    public function getAllPackageFiles()
    {
        return $this->fileList;
    }

    /**
     * Returns all found packages as objects.
     *
     * @return \SSpkS\Package\Package[] List of packages as objects.
     */
    public function getAllPackages()
    {
        $packages = array();
        foreach ($this->fileList as $file) {
            $packages[] = new Package($this->config, $file);
        }
        return $packages;
    }
}
