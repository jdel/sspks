<?php

namespace SSpkS\Handler;

use \SSpkS\Device\DeviceList;
use \SSpkS\Output\UrlFixer;
use \SSpkS\Package\PackageFinder;
use \SSpkS\Package\PackageFilter;

// if ($_SERVER['REQUEST_METHOD'] == 'GET')

class BrowserHandler implements HandlerInterface
{
    private $config;

    public function __construct(\SSpkS\Config $config)
    {
        $this->config = $config;
    }

    public function handle()
    {
        // GET-request, probably browser --> show HTML
        $arch     = trim($_GET['arch']);
        $channel  = trim($_GET['channel']);
        if ($channel != 'beta') {
            $channel = 'stable';
        }
        $fullList = trim($_GET['fulllist']);
        $packagesAvailable = array();

        $mustache = new \Mustache_Engine(array(
            'loader'          => new \Mustache_Loader_FilesystemLoader($this->config->basePath . '/data/templates'),
            'partials_loader' => new \Mustache_Loader_FilesystemLoader($this->config->basePath . '/data/templates/partials'),
            'charset'         => 'utf-8',
            'logger'          => new \Mustache_Logger_StreamLogger('php://stderr'),
        ));

        $tpl_vars = array(
            'siteName'   => $this->config->site['name'],
            'arch'       => $arch,
            'channel'    => ($channel == 'beta'),
            'requestUri' => $_SERVER['REQUEST_URI'],
            'baseUrl'    => $this->config->baseUrl,
            'fullList'   => $fullList,
        );

        if ($arch) {
            // Architecture is set --> show packages for that arch
            $pkgs = new PackageFinder($this->config->paths['packages']);
            $pkgf = new PackageFilter($pkgs->getAllPackages());
            $pkgf->setArchitectureFilter($arch);
            $pkgf->setChannelFilter($channel);
            $pkgf->setFirmwareVersionFilter(false);
            $pkgf->setOldVersionFilter(true);
            $filteredPkgList = $pkgf->getFilteredPackageList();

            $uf = new UrlFixer($this->config->baseUrl);
            $uf->fixPackageList($filteredPkgList);

            $packages = array();
            foreach ($filteredPkgList as $pkg) {
                $packages[] = $pkg->getMetadata();
            }

            $tpl_vars['packagelist'] = array_values($packages);
            $tpl = $mustache->loadTemplate('html_packagelist');
        } elseif ($fullList) {
            // No architecture, but full list of packages requested --> show simple list
            $pkgs = new PackageFinder($this->config->paths['packages']);
            $packagesList = $pkgs->getAllPackageFiles();

            // Prepare data for template
            $packages = array();
            foreach ($packagesList as $spkFile) {
                $packages[] = array(
                    'url'      => $this->config->baseUrl . $spkFile,
                    'filename' => basename($spkFile),
                );
            }
            $tpl_vars['packagelist'] = $packages;
            $tpl = $mustache->loadTemplate('html_packagelist_all');
        } else {
            // Nothing requested --> show models overview
            try {
                $deviceList = new DeviceList($this->config->paths['models']);
                $models = $deviceList->getDevices();
                if (count($models) == 0) {
                    $tpl = $mustache->loadTemplate('html_modellist_none');
                } else {
                    $tpl_vars['modellist'] = $models;
                    $tpl = $mustache->loadTemplate('html_modellist');
                }
            } catch (\Exception $e) {
                $tpl_vars['errorMessage'] = $e->getMessage();
                $tpl = $mustache->loadTemplate('html_modellist_error');
            }
        }
        echo $tpl->render($tpl_vars);
    }
}
