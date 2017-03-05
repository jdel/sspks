<?php

namespace SSpkS\Tests;

use PHPUnit\Framework\TestCase;
use SSpkS\Config;
use SSpkS\Package\Package;

class PackageTest extends TestCase
{
    private $config;
    private $tempPkg;

    public function setUp()
    {
        $this->config = new Config(__DIR__, 'example_configs/sspks.yaml');
        $this->config->paths = array_merge(
            $this->config->paths,
            array('cache' => sys_get_temp_dir() . '/')
        );
        $tempNoExt = tempnam(sys_get_temp_dir(), 'SSpkS');
        unlink($tempNoExt);   // don't need the file without ext'
        $this->tempPkg = $tempNoExt . '.tar';
        $phar = new \PharData($this->tempPkg);
        $phar->addFromString('INFO', file_get_contents(__DIR__ . '/example_package/INFO'));
        $phar->addFromString('PACKAGE_ICON.PNG', file_get_contents(__DIR__ . '/example_package/PACKAGE_ICON.PNG'));
        $phar->compress(\Phar::GZ, '.spk');
        $this->tempPkg = $tempNoExt . '.spk';
        touch($tempNoExt . '_screen_1.png');
        touch($tempNoExt . '_screen_2.png');
    }

    public function testPackageFileExtraction()
    {
        $file_spk  = $this->tempPkg;
        $file_nfo  = substr($file_spk, 0, strrpos($file_spk, '.')) . '.nfo';
        $file_icon = substr($file_spk, 0, strrpos($file_spk, '.')) . '_thumb_72.png';
        $this->assertFileExists($file_spk);
        $this->assertFileNotExists($file_nfo);
        $this->assertFileNotExists($file_icon);
        $p = new Package($this->config, $this->tempPkg);
        $p->getMetadata();
        $this->assertFileExists($file_nfo);
        $this->assertFileExists($file_icon);
    }

    public function testMetadata()
    {
        $p = new Package($this->config, $this->tempPkg);
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
        $this->assertCount(2, $p->thumbnail);
        $this->assertCount(2, $p->snapshot);
    }

    public function testHelperMethods()
    {
        $p = new Package($this->config, $this->tempPkg);
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
        new Package($this->config, 'badfilename.xyz');
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage File notexisting.spk not found!
     */
    public function testNonExistFile()
    {
        new Package($this->config, 'notexisting.spk');
    }

    public function testIconFromInfo()
    {
        $tempNoExt = tempnam(sys_get_temp_dir(), 'SSpkS');
        unlink($tempNoExt);   // Don't need file without ext
        $tempPkg = $tempNoExt . '.tar';
        $phar = new \PharData($tempPkg);
        $phar->addFromString('INFO', file_get_contents(__DIR__ . '/example_package/INFO'));
        $phar->compress(\Phar::GZ, '.spk');
        $tempPkg = $tempNoExt . '.spk';

        $p = new Package($this->config, $tempPkg);
        $file_nfo  = $tempNoExt . '.nfo';
        $file_icon = $tempNoExt . '_thumb_72.png';
        $p->getMetadata();
        $this->assertFileExists($file_icon);
        $this->assertCount(2, $p->thumbnail);
        $this->assertCount(0, $p->snapshot);
        $del_files = glob($tempNoExt . '*');
        foreach ($del_files as $f) {
            unlink($f);
        }
    }

    public function testExtraction()
    {
        $test_file = substr($this->tempPkg, 0, strrpos($this->tempPkg, '.')) . '.txt';
        $p = new Package($this->config, $this->tempPkg);
        $this->assertFileNotExists($test_file);
        $p->extractIfMissing('INFO', $test_file);
        $this->assertFileExists($test_file);
        $file_stat = stat($test_file);
        // this should not overwrite the already extracted file
        $this->assertTrue($p->extractIfMissing('INFO', $test_file));
        $this->assertEquals($file_stat, stat($test_file));
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
