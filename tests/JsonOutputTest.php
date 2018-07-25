<?php

namespace SSpkS\Tests;

use PHPUnit\Framework\TestCase;
use SSpkS\Package\Package;
use SSpkS\Output\JsonOutput;
use SSpkS\Output\UrlFixer;
use SSpkS\Config;

class JsonOutputTest extends TestCase
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
        $this->tempPkg = tempnam(sys_get_temp_dir(), 'SSpkS') . '.tar';
        $phar = new \PharData($this->tempPkg);
        $phar->addFromString('INFO', file_get_contents(__DIR__ . '/example_package/INFO'));
        $phar->compress(\Phar::GZ, '.spk');
        $tempNoExt = substr($this->tempPkg, 0, strrpos($this->tempPkg, '.'));
        $this->tempPkg = $tempNoExt . '.spk';
        touch($tempNoExt . '_screen_1.png');
        touch($tempNoExt . '_screen_2.png');
        if (file_exists('./gpgkey.asc')) {
            rename('./gpgkey.asc', './gpgkey.$$$');
        }
        file_put_contents('./gpgkey.asc', "test\n12345");
    }

    public function testExcludedServices()
    {
        $p = new Package($this->config, $this->tempPkg);
        $p->install_dep_services = 'test1 test2 test3 test4';
        $pl = array($p);

        $uf = new UrlFixer('http://prefix');
        $uf->fixPackageList($pl);

        $jo = new JsonOutput($this->config);
        $jo->setExcludedServices(array('test3'));

        $jo->outputPackages($pl, null);

        $this->expectOutputRegex('/"deppkgs":"test1 test2  test4"/');
    }

    public function testJsonConversion()
    {
        $p = new Package($this->config, $this->tempPkg);
        $pl = array($p);

        $uf = new UrlFixer('http://prefix');
        $uf->fixPackageList($pl);

        $jo = new JsonOutput($this->config);

        $jo->outputPackages($pl, null);

        $pkgMd5  = md5_file($this->tempPkg);
        $pkgSize = filesize($this->tempPkg);

        $this->expectOutputString(
            '{"packages":[{"package":"Docker","version":"1.11.1-0265","dname":"Docker","desc":"Docker is a lightweight virtualization application that ' .
            'gives you the ability to run thousands of containers created by developers from all over the world on DSM. The hugely popular built-in image repository, Docker ' .
            'Hub, allows you to find shared applications from other talented developers.","price":0,"download_count":0,"recent_download_count":0,"link":"http://prefix' . $this->tempPkg .
            '","size":' . $pkgSize . ',"md5":"' . $pkgMd5 . '","thumbnail":["http://prefix' . $p->thumbnail[0] . '","http://prefix' . $p->thumbnail[1] . '"],' .
            '"snapshot":["http://prefix' . $p->snapshot[0] . '","http://prefix' . $p->snapshot[1] . '"],"qinst":true,"qstart":true,"qupgrade":true,"depsers":null,"deppkgs"' .
            ':null,"conflictpkgs":null,"start":true,"maintainer":"Synology Inc.","maintainer_url":"http://dummy.org/","distributor":"SSpkS","distributor_url":"http://dummy.org/","support_url":"http://dummy.org/",' .
            '"changelog":"","thirdparty":true,"category":0,"subcategory":0,"type":0,"silent_install":true,"silent_uninstall":true,"silent_upgrade":true,"auto_upgrade_from":null,"beta":false}],"keyrings":["test\n12345"]}'
        );
    }

    public function tearDown()
    {
        $mask = substr($this->tempPkg, 0, strrpos($this->tempPkg, '.')) . '*';
        $del_files = glob($mask);
        foreach ($del_files as $f) {
            unlink($f);
        }
        unlink('./gpgkey.asc');
        if (file_exists('./gpgkey.$$$')) {
            rename('./gpgkey.$$$', './gpgkey.asc');
        }
    }
}
