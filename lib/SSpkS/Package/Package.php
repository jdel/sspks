<?php

namespace SSpkS\Package;

class Package
{
    private $filepath;
    private $filepathNoExt;
    private $filename;
    private $filenameNoExt;

    /**
     * @param string $filename Filename of SPK file
     */
    public function __construct($filename)
    {
        if (!preg_match('/\.spk$/', $filename)) {
            throw new \Exception('File ' . $filename . ' doesn\'t have .spk extension!');
        }
        if (!file_exists($filename)) {
            throw new \Exception('File ' . $filename . ' not found!');
        }
        $this->filepath      = $filename;
        $this->filepathNoExt = substr($filename, 0, -4);
        $this->filename      = basename($filename);
        $this->filenameNoExt = basename($filename, '.spk');
        $this->extractIfMissing('INFO', $this->filepathNoExt . '.nfo');
        $this->extractIfMissing('PACKAGE_ICON.PNG', $this->filepathNoExt . '_thumb_72.png');
    }

    /**
     * Returns metadata for this package.
     *
     * @return array Metadata.
     */
    public function getMetadata()
    {
        $packageInfo = parse_ini_file($this->filepathNoExt . '.nfo');
        if (!isset($packageInfo['displayname'])) {
            $packageInfo['displayname'] = $packageInfo['package'];
        }
        $packageInfo['nfo']       = $this->filepathNoExt . '.nfo';
        $packageInfo['spk']       = $this->filepath;
        $packageInfo['thumbnail'] = $this->getThumbnails();
        $packageInfo['snapshot']  = $this->getSnapshots();

        // Convert architecture(s) to array, as multiple architectures can be specified
        $packageInfo['arch'] = explode(' ', $packageInfo['arch']);
        return $packageInfo;
    }
 
    /**
     * Extracts $inPkgName from package to $targetFile, if it doesn't
     * already exist. Needs the phar.so extension and allow_url_fopen.
     *
     * @param string $inPkgName Filename in package
     * @param string $targetFile Path to destination
     * @throws \Exception if the file couldn't get extracted.
     * @return bool TRUE if successful or no action needed.
     */
    public function extractIfMissing($inPkgName, $targetFile)
    {
        if (file_exists($targetFile)) {
            // Everything in working order
            return true;
        }
        // Try to extract .nfo file
        copy('phar://' . $this->filepath . '/' . $inPkgName, $targetFile);
        if (!file_exists($targetFile)) {
            throw new \Exception('Could not extract ' . $inPkgName . ' from ' . $this->filepath . '!');
        }
        return true;
    }

    /**
     * Returns a list of thumbnails for the specified package.
     *
     * @param string $pathPrefix Prefix to put before file path
     * @return array List of thumbnail urls
     */
    public function getThumbnails($pathPrefix = '')
    {
        $thumbnails = array();
        foreach (array('72', '120') as $size) {
            $thumb_name = $this->filepathNoExt . '_thumb_' . $size . '.png';
            // Use $size px thumbnail, if available
            if (file_exists($thumb_name)) {
                $thumbnails[] = $pathPrefix . $thumb_name;
            } else {
                $thumbnails[] = $pathPrefix . dirname($thumb_name) . '/default_package_icon_' . $size . '.png';
            }
        }
        return $thumbnails;
    }

    /**
     * Returns a list of screenshots for the specified package.
     *
     * @param string $pathPrefix Prefix to put before file path
     * @return array List of screenshots
     */
    public function getSnapshots($pathPrefix = '')
    {
        $snapshots = array();
        // Add screenshots, if available
        foreach (glob($this->filepathNoExt . '*_screen_*.png') as $snapshot) {
            $snapshots[] = $pathPrefix . $snapshot;
        }
        return $snapshots;
    }
}
