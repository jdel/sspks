<?php

namespace SSpkS\Tests;

use PHPUnit\Framework\TestCase;
use SSpkS\Package\Package;
use SSpkS\Output\UrlFixer;

class UrlFixerTest extends TestCase
{
    private $tempPkg;

    public function setUp()
    {
        $this->tempPkg = tempnam(sys_get_temp_dir(), 'SSpkS') . '.tar';
        $phar = new \PharData($this->tempPkg);
        $phar->addFromString('INFO', file_get_contents(__DIR__ . '/example_package/INFO'));
        $phar->compress(\Phar::GZ, '.spk');
        $tempNoExt = substr($this->tempPkg, 0, strrpos($this->tempPkg, '.'));
        $this->tempPkg = $tempNoExt . '.spk';
        touch($tempNoExt . '_screen_1.png');
        touch($tempNoExt . '_screen_2.png');
    }

    public function testSinglePackageFix()
    {
        $tempNoExt = substr($this->tempPkg, 0, strrpos($this->tempPkg, '.'));
        $p = new Package($this->tempPkg);

        $thumb0 = $tempNoExt . '_thumb_72.png';
        $thumb1 = dirname($tempNoExt) . '/default_package_icon_120.png';
        $this->assertEquals($thumb0, $p->thumbnail[0]);
        $this->assertEquals($thumb1, $p->thumbnail[1]);
        $this->assertFalse(isset($p->thumbnail_url));

        $snap0 = $tempNoExt . '_screen_1.png';
        $snap1 = $tempNoExt . '_screen_2.png';
        $this->assertEquals($snap0, $p->snapshot[0]);
        $this->assertEquals($snap1, $p->snapshot[1]);
        $this->assertFalse(isset($p->snapshot_url));

        $prefix = 'http://prefix';
        $uf = new UrlFixer($prefix);
        $uf->fixPackage($p);
        $this->assertTrue(isset($p->thumbnail_url));
        $this->assertCount(count($p->thumbnail), $p->thumbnail_url);
        $this->assertEquals($prefix . $thumb0, $p->thumbnail_url[0]);
        $this->assertEquals($prefix . $thumb1, $p->thumbnail_url[1]);

        $this->assertTrue(isset($p->snapshot_url));
        $this->assertCount(count($p->snapshot), $p->snapshot_url);
        $this->assertEquals($prefix . $snap0, $p->snapshot_url[0]);
        $this->assertEquals($prefix . $snap1, $p->snapshot_url[1]);
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
