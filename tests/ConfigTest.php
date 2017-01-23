<?php

namespace SSpkS\Tests;

use PHPUnit\Framework\TestCase;
use SSpkS\Config;

class ConfigTest extends TestCase
{
    private $goodFile = 'example_configs/sspks.yaml';
    private $badFile  = 'example_configs/sspks_bad.yaml';

    /**
     * @expectedException \Exception
     * @expectedExceptionMessageRegExp /^Config file ".*nonexist.yaml" not found!$/
     */
    public function testNonExistYaml()
    {
        new Config(__DIR__, 'nonexist.yaml');
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Unable to parse at line 6 (near "packages pancakes").
     */
    public function testBadYaml()
    {
        new Config(__DIR__, $this->badFile);
    }

    public function testYaml()
    {
        $cfg = new Config(__DIR__, $this->goodFile);
        $this->assertCount(5, $cfg->excludedSynoServices);
        $this->assertEquals(array('name' => 'Test config file'), $cfg->site);
        $this->assertContains('service5', $cfg->excludedSynoServices);
    }
}
