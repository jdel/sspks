<?php

namespace SSpkS\Tests;

use PHPUnit\Framework\TestCase;
use SSpkS\Config;
use SSpkS\Device\DeviceList;

class DeviceListTest extends TestCase
{
    private $config;
    private $goodFile = __DIR__ . '/example_devicelists/models.yaml';
    private $badFile  = __DIR__ . '/example_devicelists/models_bad.yaml';

    public function setUp()
    {
        $this->config = new Config(__DIR__, '/example_configs/sspks.yaml');
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage DeviceList file nonexist.yaml not found!
     */
    public function testNonExistYaml()
    {
        $this->config->paths = array('models' => 'nonexist.yaml');
        new DeviceList($this->config);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Unable to parse at line 2 (near "architecture").
     */
    public function testBadYaml()
    {
        $this->config->paths = array('models' => $this->badFile);
        new DeviceList($this->config);
    }

    public function testYaml()
    {
        $this->config->paths = array('models' => $this->goodFile);
        $dl = new DeviceList($this->config);
        $d  = $dl->getDevices();
        $this->assertCount(6, $d);
        $this->assertContainsOnly('array', $d);
        $this->assertContains(array('arch' => 'architecture2', 'name' => 'model4', 'family' => 'family1'), $d);
    }

    public function testPlusSigns()
    {
        $this->config->paths = array('models' => $this->goodFile);
        $dl = new DeviceList($this->config);
        $d  = $dl->getDevices();
        $this->assertContains(array('arch' => 'plussign', 'name' => 'DS411+II', 'family' => 'family2'), $d);
        $this->assertContains(array('arch' => 'plussign', 'name' => 'DS211+', 'family' => 'family2'), $d);
    }
}
