<?php

namespace SSpkS\Handler;

use \SSpkS\Device\DeviceList;
use \SSpkS\Output\HtmlOutput;

class BrowserDeviceListHandler extends AbstractHandler
{
    public function canHandle()
    {
        return ($_SERVER['REQUEST_METHOD'] == 'GET');
    }

    public function handle()
    {
        // Nothing requested --> show models overview
        $output = new HtmlOutput($this->config);
        try {
            $deviceList = new DeviceList($this->config);
            $models = $deviceList->getDevices();
            if (count($models) == 0) {
                $output->setTemplate('html_modellist_none');
            } else {
                $output->setVariable('modellist', $models);
                $output->setTemplate('html_modellist');
            }
        } catch (\Exception $e) {
            $output->setVariable('errorMessage', $e->getMessage());
            $output->setTemplate('html_modellist_error');
        }
        $output->setVariable('is_devicelist', true);
        $output->output();
    }
}
