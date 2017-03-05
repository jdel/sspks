<?php

namespace SSpkS\Tests;

use PHPUnit\Framework\TestCase;
use SSPkS\Config;
use SSpkS\Package\PackageFinder;

class PackageFinderTest extends TestCase
{
    private $config;
    private $testFolder = __DIR__ . '/example_packageset';

    public function setUp()
    {
        $this->config = new Config(__DIR__, 'example_configs/sspks.yaml');
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage /nonexistingfolder is not a folder!
     */
    public function testNotExistFolder()
    {
        new PackageFinder($this->config, '/nonexistingfolder');
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessageRegExp /.+ is not a folder!/
     */
    public function testFileInsteadFolder()
    {
        new PackageFinder($this->config, __FILE__);
    }

    public function testFilelist()
    {
        $pf = new PackageFinder($this->config, $this->testFolder);
        $fl = $pf->getAllPackageFiles();
        $this->assertCount(5, $fl);
        foreach ($fl as $f) {
            $this->assertStringEndsWith('.spk', $f);
            $this->assertFileExists($f);
        }
    }

    public function testPackageList()
    {
        $pf = new PackageFinder($this->config, $this->testFolder);
        $pl = $pf->getAllPackages();
        $this->assertCount(5, $pl);
        foreach ($pl as $p) {
            $this->assertInstanceOf(\SSpkS\Package\Package::class, $p);
        }
    }
}
