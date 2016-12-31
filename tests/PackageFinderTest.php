<?php

namespace SSpkS\Tests;

use PHPUnit\Framework\TestCase;
use SSpkS\Package\PackageFinder;

class PackageFinderTest extends TestCase
{
    private $testFolder = __DIR__ . '/example_packageset';

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage /nonexistingfolder is not a folder!
     */
    public function testNotExistFolder()
    {
        new PackageFinder('/nonexistingfolder');
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessageRegExp /.+ is not a folder!/
     */
    public function testFileInsteadFolder()
    {
        new PackageFinder(__FILE__);
    }

    public function testFilelist()
    {
        $pf = new PackageFinder($this->testFolder);
        $fl = $pf->getAllPackageFiles();
        $this->assertCount(5, $fl);
        foreach ($fl as $f) {
            $this->assertStringEndsWith('.spk', $f);
            $this->assertFileExists($f);
        }
    }

    public function testPackageList()
    {
        $pf = new PackageFinder($this->testFolder);
        $pl = $pf->getAllPackages();
        $this->assertCount(5, $pl);
        foreach ($pl as $p) {
            $this->assertInstanceOf(\SSpkS\Package\Package::class, $p);
        }
    }
}
