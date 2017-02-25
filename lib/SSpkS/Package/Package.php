<?php

namespace SSpkS\Package;

/**
 * SPK Package class
 *
 * @property string $spk Path to SPK file
 * @property string $spk_url URL to SPK file
 * @property string $displayname Pretty printed name of package (falls back to $package if not present)
 * @property string $package Package name
 * @property string $version Package version
 * @property string $description Package description
 * @property string $maintainer Package maintainer
 * @property string $maintainer_url URL of maintainer's web page
 * @property string $distributor Package distributor
 * @property string $distributor_url URL of distributor's web page
 * @property array $arch List of supported architectures, or 'noarch'
 * @property array $thumbnail List of thumbnail files
 * @property array $thumbnail_url List of thumbnail URLs
 * @property array $snapshot List of screenshot files
 * @property array $snapshot_url List of screenshot URLs
 * @property bool $beta TRUE if this is a beta package.
 * @property string $firmware Minimum firmware needed on device.
 * @property string $install_dep_services Dependencies required by this package.
 * @property bool $silent_install Allow silent install
 * @property bool $silent_uninstall Allow silent uninstall
 * @property bool $silent_upgrade Allow silent upgrade
 */
class Package
{
    private $filepath;
    private $filepathNoExt;
    private $filename;
    private $filenameNoExt;
    private $metafile;
    private $metadata;

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
        $this->metafile      = $this->filepathNoExt . '.nfo';
    }

    /**
     * Getter magic method.
     *
     * @param string $name Name of requested value.
     * @return mixed Requested value.
     */
    public function __get($name)
    {
        $this->collectMetadata();
        return $this->metadata[$name];
    }

    /**
     * Setter magic method.
     *
     * @param string $name Name of variable to set.
     * @param mixed $value Value to set.
     */
    public function __set($name, $value)
    {
        $this->collectMetadata();
        $this->metadata[$name] = $value;
    }

    /**
     * Isset feature magic method.
     *
     * @param string $name Name of requested value.
     * @return bool TRUE if value exists, FALSE otherwise.
     */
    public function __isset($name)
    {
        $this->collectMetadata();
        return isset($this->metadata[$name]);
    }

    /**
     * Unset feature magic method.
     *
     * @param string $name Name of value to unset.
     */
    public function __unset($name)
    {
        $this->collectMetadata();
        unset($this->metadata[$name]);
    }

    /**
     * Parses boolean value ('yes', '1', 'true') into
     * boolean type.
     *
     * @param mixed $value Input value
     * @return bool Boolean interpretation of $value.
     */
    public function parseBool($value)
    {
        return in_array($value, array('true', 'yes', '1', 1));
    }

    /**
     * Checks if given property $prop exists and converts it
     * into a boolean value.
     *
     * @param string $prop Property to convert
     */
    private function fixBoolIfExist($prop)
    {
        if (isset($this->metadata[$prop])) {
            $this->metadata[$prop] = $this->parseBool($this->metadata[$prop]);
        }
    }

    /**
     * Gathers metadata from package. Extracts INFO file if neccessary.
     */
    private function collectMetadata()
    {
        if (!is_null($this->metadata)) {
            // metadata already collected
            return;
        }
        $this->extractIfMissing('INFO', $this->metafile);
        $this->metadata = parse_ini_file($this->metafile);
        if (!isset($this->metadata['displayname'])) {
            $this->metadata['displayname'] = $this->metadata['package'];
        }
        $this->metadata['spk'] = $this->filepath;

        // Convert architecture(s) to array, as multiple architectures can be specified
        $this->metadata['arch'] = explode(' ', $this->metadata['arch']);

        $this->fixBoolIfExist('silent_install');
        $this->fixBoolIfExist('silent_uninstall');
        $this->fixBoolIfExist('silent_upgrade');

        if (isset($this->metadata['beta']) && in_array($this->metadata['beta'], array('true', '1', 'beta'))) {
            $this->metadata['beta'] = true;
        } else {
            $this->metadata['beta'] = false;
        }

        $this->metadata['thumbnail'] = $this->getThumbnails();
        $this->metadata['snapshot']  = $this->getSnapshots();
    }

    /**
     * Returns metadata for this package.
     *
     * @return array Metadata.
     */
    public function getMetadata()
    {
        $this->collectMetadata();
        return $this->metadata;
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
        // Try to extract file
        $tmp_dir = ini_get('upload_tmp_dir') ? ini_get('upload_tmp_dir') : sys_get_temp_dir();
        $free_tmp = disk_free_space($tmp_dir);
        if ($free_tmp < 2048) {
            throw new \Exception('TMP folder only has ' . $free_tmp . ' Bytes space available. Disk full!');
        }
        $free = disk_free_space(dirname($targetFile));
        if ($free < 2048) {
            throw new \Exception('Package folder only has ' . $free . ' Bytes space available. Disk full!');
        }
        try {
            $p = new \PharData($this->filepath, \Phar::CURRENT_AS_FILEINFO | \Phar::KEY_AS_FILENAME);
        } catch (\UnexpectedValueException $e) {
            rename($this->filepath, $this->filepath . '.invalid');
            throw new \Exception('Package ' . $this->filepath . ' not readable! Will be ignored in the future. Please try again!');
        }
        $p->extractTo($tmp_dir, $inPkgName);
        rename($tmp_dir . DIRECTORY_SEPARATOR . $inPkgName, $targetFile);
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
        try {
            $this->extractIfMissing('PACKAGE_ICON.PNG', $this->filepathNoExt . '_thumb_72.png');
        } catch (\Exception $e) {
            // Check if icon is in metadata
            $this->collectMetadata();
            if (isset($this->metadata['package_icon'])) {
                file_put_contents($this->filepathNoExt . '_thumb_72.png', base64_decode($this->metadata['package_icon']));
            }
        }
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

    /**
     * Checks compatibility to the given $arch-itecture.
     *
     * @param string $arch Architecture to check against (or "noarch")
     * @return bool TRUE if compatible, otherwise FALSE.
     */
    public function isCompatibleToArch($arch)
    {
        // Make sure we have metadata available
        $this->collectMetadata();
        return (in_array($arch, $this->metadata['arch']) || in_array('noarch', $this->metadata['arch']));
    }

    /**
     * Checks compatibility to the given firmware $version.
     *
     * @param string $version Target firmware version.
     * @return bool TRUE if compatible, otherwise FALSE.
     */
    public function isCompatibleToFirmware($version)
    {
        $this->collectMetadata();
        return version_compare($this->metadata['firmware'], $version, '<=');
    }

    /**
     * Checks if this package is a beta version or not.
     *
     * @return bool TRUE if this is a beta version, FALSE otherwise.
     */
    public function isBeta()
    {
        $this->collectMetadata();
        return (isset($this->metadata['beta']) && $this->metadata['beta'] == true);
    }
}
