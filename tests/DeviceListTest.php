<?php

namespace SSpkS\Tests;

use PHPUnit\Framework\TestCase;
use SSpkS\Device\DeviceList;

class DeviceListTest extends TestCase
{
    private $goodFile = __DIR__ . '/example_devicelists/models.yaml';
    private $badFile  = __DIR__ . '/example_devicelists/models_bad.yaml';

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage DeviceList file nonexist.yaml not found!
     */
    public function testNonExistYaml()
    {
        new DeviceList('nonexist.yaml');
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Unable to parse at line 1 (near "architecture").
     */
    public function testBadYaml()
    {
        new DeviceList($this->badFile);
    }

    public function testYaml()
    {
        $dl = new DeviceList($this->goodFile);
        $d  = $dl->getDevices();
        $this->assertCount(6, $d);
        $this->assertContainsOnly('array', $d);
        $this->assertContains(array('arch' => 'architecture2', 'name' => 'model4'), $d);
    }

    public function testPlusSigns()
    {
        $dl = new DeviceList($this->goodFile);
        $d  = $dl->getDevices();
        $this->assertContains(array('arch' => 'plussign', 'name' => 'DS411+II'), $d);
        $this->assertContains(array('arch' => 'plussign', 'name' => 'DS211+'), $d);
    }
}
