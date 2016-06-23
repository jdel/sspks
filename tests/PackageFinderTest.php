<?php

namespace SSpkS\Tests;

use PHPUnit\Framework\TestCase;
use SSpkS\Package\PackageFinder;

class PackageFinderTest extends TestCase
{
    private $testFolder = __DIR__ . '/example_packageset/';

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage /nonexistingfolder is not a folder!
     */
    public function testNotExistFolder()
    {
        $pf = new PackageFinder('/nonexistingfolder');
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessageRegExp /.+ is not a folder!/
     */
    public function testFileInsteadFolder()
    {
        $pf = new PackageFinder(__FILE__);
    }
}
