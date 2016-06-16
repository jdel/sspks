<?php

namespace SSpkS\Package;

class Package
{
    private $filepath;
    private $filepath_noext;
    private $filename;
    private $filename_noext;

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
        $this->filepath       = $filename;
        $this->filepath_noext = substr($filename, 0, -4);
        $this->filename       = basename($filename);
        $this->filename_noext = basename($filename, '.spk');
        $this->checkMetafiles();
    }

    public function checkMetafiles()
    {
        $nfoFile = $this->filepath_noext . '.nfo';
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
}
