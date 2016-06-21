<?php

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
        $phar2 = $phar->compress(\Phar::GZ, '.spk');
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
        $md = $p->getMetadata();
        $this->assertFileExists($file_nfo);
        $this->assertFileExists($file_icon);
    }

    public function testMetadata()
    {
        $p = new Package($this->tempPkg);
        $md = $p->getMetadata();
        $this->assertEquals($p->package, 'Docker');
        $this->assertEquals($md['version'], '1.11.1-0265');
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