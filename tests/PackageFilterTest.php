<?php

namespace SSpkS\Tests;

use PHPUnit\Framework\TestCase;
use SSpkS\Package\PackageFinder;
use SSpkS\Package\PackageFilter;

class PackageFilterTest extends TestCase
{
    private $testFolder = __DIR__ . '/example_packageset/';
    private $testList;

    public function setUp()
    {
        $pf = new PackageFinder($this->testFolder);
        $this->testList = $pf->getAllPackages();
    }

    public function testPassThru()
    {
        $pf = new PackageFilter($this->testList);
        $pl = $pf->getFilteredPackageList();
        $this->assertContainsOnlyInstancesOf(\SSpkS\Package\Package::class, $pl);
        $this->assertEquals($pl, $this->testList);
    }

    public function testOmitOldVersions()
    {
        $pf = new PackageFilter($this->testList);
        $pf->setOldVersionFilter(true);
        $newList = $pf->getFilteredPackageList();
        // 2 files are dupes
        $this->assertCount(count($this->testList)-2, $newList);
    }

    public function testArchitectureFilter()
    {
        $pf = new PackageFilter($this->testList);
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

    public function tearDown()
    {
        $del = array_merge(glob($this->testFolder . '*.nfo'), glob($this->testFolder . '*.png'));
        foreach ($del as $file) {
            unlink($file);
        }
    }
}
