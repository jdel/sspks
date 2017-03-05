<?php

namespace SSpkS\Tests;

use PHPUnit\Framework\TestCase;
use SSpkS\Config;
use SSpkS\Package\PackageFinder;
use SSpkS\Package\PackageFilter;

class PackageFilterTest extends TestCase
{
    private $config;
    private $testFolder = __DIR__ . '/example_packageset/';
    private $testList;

    public function setUp()
    {
        $this->config = new Config(__DIR__, 'example_configs/sspks.yaml');
        $this->config->paths = array_merge(
            $this->config->paths,
            array('packages' => $this->testFolder)
        );
        $pf = new PackageFinder($this->config);
        $this->testList = $pf->getAllPackages();
    }

    public function testPassThru()
    {
        $pf = new PackageFilter($this->config, $this->testList);
        $pl = $pf->getFilteredPackageList();
        $this->assertContainsOnlyInstancesOf(\SSpkS\Package\Package::class, $pl);
        $this->assertEquals($pl, $this->testList);
    }

    public function testOmitOldVersions()
    {
        $pf = new PackageFilter($this->config, $this->testList);
        $pf->setOldVersionFilter(true);
        $newList = $pf->getFilteredPackageList();
        // 2 files are dupes
        $this->assertCount(count($this->testList)-2, $newList);
    }

    public function testArchitectureFilter()
    {
        $pf = new PackageFilter($this->config, $this->testList);
        $pf->setArchitectureFilter('x86_64');
        $newList = $pf->getFilteredPackageList();
        foreach ($newList as $pkg) {
            $this->assertContains($pkg->arch[0], 'x86_64 noarch');
        }

        $pf->setArchitectureFilter('avoton');
        $newList = $pf->getFilteredPackageList();
        // expect avoton + noarch packages: 3
        $this->assertCount(3, $newList);
    }

    public function testFirmwareVersionFilter()
    {
        $pf = new PackageFilter($this->config, $this->testList);
        $pf->setFirmwareVersionFilter('1.0-0000');
        $newList = $pf->getFilteredPackageList();
        $this->assertCount(3, $newList);
        $pf->setFirmwareVersionFilter('0.9-9999');
        $newList = $pf->getFilteredPackageList();
        $this->assertCount(0, $newList);
        $pf->setFirmwareVersionFilter('1.0-1233');
        $newList = $pf->getFilteredPackageList();
        $this->assertCount(4, $newList);
        $pf->setFirmwareVersionFilter('1.0-1234');
        $newList = $pf->getFilteredPackageList();
        $this->assertCount(5, $newList);
    }

    public function testChannelFilter()
    {
        $pf = new PackageFilter($this->config, $this->testList);
        $pf->setChannelFilter('stable');
        $newList = $pf->getFilteredPackageList();
        $this->assertCount(4, $newList);
        $pf->setChannelFilter('beta');
        $newList = $pf->getFilteredPackageList();
        $this->assertCount(5, $newList);
        $pf->setChannelFilter('invalid');
        $newList = $pf->getFilteredPackageList();
        $this->assertCount(0, $newList);
    }

    public function tearDown()
    {
        $del = array_merge(glob($this->testFolder . '*.nfo'), glob($this->testFolder . '*.png'));
        foreach ($del as $file) {
            unlink($file);
        }
    }
}
