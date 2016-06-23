<?php

namespace SSpkS\Tests;

use PHPUnit\Framework\TestCase;
use SSpkS\Package\Package;

class PackageTest extends TestCase
{
    private $tempPkg;

    public function setUp()
    {
        $this->tempPkg = tempnam(sys_get_temp_dir(), 'SSpkS') . '.tar';
        $phar = new \PharData($this->tempPkg);
        $phar->addFromString('INFO', file_get_contents(__DIR__ . '/example_package/INFO'));
        $phar->addFromString('PACKAGE_ICON.PNG', file_get_contents(__DIR__ . '/example_package/PACKAGE_ICON.PNG'));
        $phar->compress(\Phar::GZ, '.spk');
        $this->tempPkg = substr($this->tempPkg, 0, strrpos($this->tempPkg, '.')) . '.spk';
    }

    public function testPackageFileExtraction()
    {
        $file_spk  = $this->tempPkg;
        $file_nfo  = substr($file_spk, 0, strrpos($file_spk, '.')) . '.nfo';
        $file_icon = substr($file_spk, 0, strrpos($file_spk, '.')) . '_thumb_72.png';
        $this->assertFileExists($file_spk);
        $this->assertFileNotExists($file_nfo);
        $this->assertFileNotExists($file_icon);
        $p = new Package($this->tempPkg);
        $p->getMetadata();
        $this->assertFileExists($file_nfo);
        $this->assertFileExists($file_icon);
    }

    public function testMetadata()
    {
        $p = new Package($this->tempPkg);
        $md = $p->getMetadata();
        $this->assertGreaterThan(0, count($md));
        $this->assertEquals($p->package, 'Docker');
        $this->assertTrue(isset($p->displayname));
        $this->assertEquals($p->displayname, 'Docker');
        $this->assertEquals($md['version'], '1.11.1-0265');
        $p->newprop = 'test';
        $this->assertTrue(isset($p->newprop));
        $this->assertEquals($p->newprop, 'test');
        unset($p->newprop);
        $this->assertFalse(isset($p->newprop));
        $this->assertObjectNotHasAttribute('newprop', $p);
        $this->assertTrue($p->silent_install);
        $this->assertTrue($p->silent_uninstall);
        $this->assertTrue($p->silent_upgrade);
    }

    public function testHelperMethods()
    {
        $p = new Package($this->tempPkg);
        $this->assertTrue($p->isCompatibleToArch('x86_64'));
        $this->assertFalse($p->isCompatibleToArch('avoton'));
        $this->assertTrue($p->isCompatibleToFirmware('6.0.1-7393'));
        $this->assertFalse($p->isCompatibleToFirmware('6.0.0'));
        $this->assertFalse($p->isBeta());
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage File badfilename.xyz doesn't have .spk extension!
     */
    public function testBadFilename()
    {
        new Package('badfilename.xyz');
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage File notexisting.spk not found!
     */
    public function testNonExistFile()
    {
        new Package('notexisting.spk');
    }

    public function testIconFromInfo()
    {
        $tempPkg = tempnam(sys_get_temp_dir(), 'SSpkS') . '.tar';
        $phar = new \PharData($tempPkg);
        $phar->addFromString('INFO', file_get_contents(__DIR__ . '/example_package/INFO'));
        $phar->compress(\Phar::GZ, '.spk');
        $tempNoExt = substr($tempPkg, 0, strrpos($tempPkg, '.'));
        $tempPkg = $tempNoExt . '.spk';

        $p = new Package($tempPkg);
        $file_nfo  = $tempNoExt . '.nfo';
        $file_icon = $tempNoExt . '_thumb_72.png';
        $p->getMetadata();
        $this->assertFileExists($file_icon);
        unlink($tempPkg);
        unlink($file_nfo);
        unlink($file_icon);
    }

    public function tearDown()
    {
        $mask = substr($this->tempPkg, 0, strrpos($this->tempPkg, '.')) . '*';
        $del_files = glob($mask);
        foreach ($del_files as $f) {
            unlink($f);
        }
    }
}
