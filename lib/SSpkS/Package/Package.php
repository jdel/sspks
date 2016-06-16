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
        $this->checkMetafiles();
        $this->checkIcon();
    }

    public function checkMetafiles()
    {
        $nfoFile = $this->filepathNoExt . '.nfo';
        if (file_exists($nfoFile)) {
            // Everything in working order
            return true;
        }
        // Try to extract .nfo file
        copy('phar://' . $this->filepath . '/INFO', $nfoFile);
        if (!file_exists($nfoFile)) {
            throw new \Exception('Could not extract INFO from ' . $this->filepath . '!');
        }
    }

    public function checkIcon()
    {
        $iconFile = $this->filepathNoExt . '_thumb_72.png';
        if (file_exists($iconFile)) {
            // Everything in working order
            return true;
        }
        // Try to extract icon
        copy('phar://' . $this->filepath . '/PACKAGE_ICON.PNG', $iconFile);
        if (!file_exists($iconFile)) {
            throw new \Exception('Could not extract PACKAGE_ICON.PNG from ' . $this->filepath . '!');
        }
    }
}
