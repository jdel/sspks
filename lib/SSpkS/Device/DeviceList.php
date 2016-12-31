<?php

namespace SSpkS\Device;

use \Symfony\Component\Yaml\Yaml;
use \Symfony\Component\Yaml\Exception\ParseException;

class DeviceList
{
    private $yamlFilepath;
    private $devices = array();

    /**
     * @param string $yamlFilepath Filename of Yaml file containing model list
     * @throws \Exception if file is not found or parsing error.
     */
    public function __construct($yamlFilepath)
    {
        $this->yamlFilepath = $yamlFilepath;
        if (!file_exists($this->yamlFilepath)) {
            throw new \Exception('DeviceList file ' . $this->yamlFilepath . ' not found!');
        }
        try {
            $this->parseYaml();
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Parse Yaml file with device data.
     *
     * @throws \Exception if Yaml couldn't be parsed.
     */
    private function parseYaml()
    {
        try {
            /** @var array $archlist */
            $archlist = Yaml::parse(file_get_contents($this->yamlFilepath));
        } catch (ParseException $e) {
            throw new \Exception($e->getMessage());
        }
        $idx = 0;
        $sortkey = array();
        foreach ($archlist as $arch => $archmodels) {
            foreach ($archmodels as $model) {
                $this->devices[$idx] = array(
                    'arch' => $arch,
                    'name' => $model,
                );
                $sortkey[$idx] = $model;
                $idx++;
            }
        }
        array_multisort($sortkey, SORT_NATURAL|SORT_FLAG_CASE, $this->devices);
    }

    /**
     * Returns the list of devices and their architectures.
     *
     * @return array List of devices and architectures.
     */
    public function getDevices()
    {
        return $this->devices;
    }
}
