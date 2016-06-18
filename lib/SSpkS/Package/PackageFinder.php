<?php

namespace SSpkS\Package;

use Package;

/**
 * SPK PackageFinder class
 */
class PackageFinder
{
    private $fileGlob;
    private $baseFolder;
    private $fileList;

    /**
     * @param string $folder Folder to search for SPK files
     * @param string $glob Filemask for package files (default: '*.spk')
     * @throws \Exception if $folder is not a folder.
     */
    public function __construct($folder, $glob = '*.spk')
    {
        if (!file_exists($folder) && !is_dir($folder)) {
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

}
