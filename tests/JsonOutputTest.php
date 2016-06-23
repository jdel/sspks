<?php

namespace SSpkS\Tests;

use PHPUnit\Framework\TestCase;
use SSpkS\Package\Package;
use SSpkS\Output\JsonOutput;
use SSpkS\Output\UrlFixer;

class JsonOutputTest extends TestCase
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

    public function testExcludedServices()
    {
        $p = new Package($this->tempPkg);
        $p->install_dep_services = 'test1 test2 test3 test4';
        $pl = array($p);

        $uf = new UrlFixer('http://prefix');
        $uf->fixPackageList($pl);

        $jo = new JsonOutput();
        $jo->setExcludedServices(array('test3'));

        $jo->outputPackages($pl);

        $this->expectOutputRegex('/"deppkgs":"test1 test2  test4"/');
    }

    public function testJsonConversion()
    {
        $p = new Package($this->tempPkg);
        $pl = array($p);

        $uf = new UrlFixer('http://prefix');
        $uf->fixPackageList($pl);

        $jo = new JsonOutput();

        $jo->outputPackages($pl);

        $pkgMd5  = md5_file($this->tempPkg);
        $pkgSize = filesize($this->tempPkg);

        $this->expectOutputString('{"packages":[{"package":"Docker","version":"1.11.1-0265","dname":"Docker","desc":"Docker is a lightweight virtualization application that ' .
            'gives you the ability to run thousands of containers created by developers from all over the world on DSM. The hugely popular built-in image repository, Docker ' .
            'Hub, allows you to find shared applications from other talented developers.","price":0,"download_count":6000,"recent_download_count":1222,"link":"http://prefix' . $this->tempPkg .
            '","size":' . $pkgSize . ',"md5":"' . $pkgMd5 . '","thumbnail":["http://prefix' . $p->thumbnail[0] . '","http://prefix' . $p->thumbnail[1] . '"],' .
            '"snapshot":["http://prefix' . $p->snapshot[0] . '","http://prefix' . $p->snapshot[1] . '"],"qinst":false,"qstart":false,"qupgrade":false,"depsers":null,"deppkgs"' .
            ':null,"conflictpkgs":null,"start":true,"maintainer":"SSpkS","maintainer_url":"http://dummy.org/","distributor":"SSpkS","distributor_url":"http://dummy.org/",' .
            '"changelog":"","thirdparty":true,"category":0,"subcategory":0,"type":0,"silent_install":false,"silent_uninstall":false,"silent_upgrade":false,"beta":false}]}');
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
