<?php

namespace Toolbox\View\Helper;

use Toolbox\Config;
use Pimcore\ExtensionManager;

class ToolboxHelper extends \Zend_View_Helper_Abstract
{
    /**
     * @return $this
     */
    public function toolboxHelper()
    {
        return $this;
    }

    /**
     * @param string|array $areaType toolbox element or custom config
     * @param null|object  $element  related element to track
     *
     * @return string
     */
    public function addTracker($areaType, $element = NULL)
    {
        if (empty($areaType)) {
            return '';
        }

        if (is_array($areaType))  //custom data
        {
            $trackerInfo = $areaType;
        } else //area data
        {
            $configNode = Config::getConfig()->{$areaType};

            if (empty($configNode)) {
                return '';
            }

            $configInfo = $configNode->toArray();

            if (!isset($configInfo['eventTracker'])) {
                return '';
            }

            $trackerInfo = $configInfo['eventTracker'];
        }

        $str = 'data-tracking="active" ';

        $str .= join(' ', array_map(function ($key) use ($trackerInfo, $element) {
            $val = $trackerInfo[$key];

            if (is_bool($val)) {
                $val = (int)$val;
            }

            if ($key === 'label' && is_array($val)) {
                //userfunc. 0 => (string) method, 1 = (array) arguments
                $getter = $val;
                $val = call_user_func_array([$element, $getter[0]], $getter[1]);

                if (empty($val)) {
                    $val = 'no label given';
                }
            }

            return 'data-' . $key . '="' . $val . '"';
        }, array_keys($trackerInfo)));

        return $str;
    }

    /**
     * @param $data
     *
     * @deprecated
     * @return array
     */
    public function getAssetArray($data)
    {
        if (empty($data)) {
            return [];
        }

        $assets = [];

        foreach ($data as $element) {
            if ($element instanceof \Pimcore\Model\Asset\Image) {
                $assets[] = $element;
            } else if ($element instanceof \Pimcore\Model\Asset\Folder) {
                foreach ($element->getChilds() as $child) {
                    if ($child instanceof \Pimcore\Model\Asset\Image) {
                        $assets[] = $child;
                    }
                }
            }
        }

        return $assets;
    }

    /**
     * @param $columnType
     *
     * @return string
     */
    public function calculateSlideColumnClasses($columnType)
    {
        $columnType = (int)$columnType;
        $configNode = Config::getConfig()->slideColumns;

        $systemClasses = [
            2 => 'col-xs-12 col-sm-6',
            3 => 'col-xs-12 col-sm-4',
            4 => 'col-xs-12 col-sm-3',
            6 => 'col-xs-12 col-sm-2',

        ];

        if (empty($configNode)) {
            return isset($systemClasses[$columnType]) ? $systemClasses[$columnType] : 'col-xs-12';
        }

        $configInfo = $configNode->toArray();
        if (!isset($configInfo['columnClasses']) || !isset($configInfo['columnClasses'][$columnType])) {
            return isset($systemClasses[$columnType]) ? $systemClasses[$columnType] : 'col-xs-12';
        }

        return $configInfo['columnClasses'][$columnType];
    }

    /**
     * @param $columnType
     *
     * @return array
     */
    public function calculateSlideColumnBreakpoints($columnType)
    {
        $columnType = (int)$columnType;
        $configNode = Config::getConfig()->slideColumns;

        $breakpoints = [];

        if (!empty($configNode)) {
            $configInfo = $configNode->toArray();

            if (isset($configInfo['breakpoints']) && isset($configInfo['breakpoints'][$columnType])) {
                $breakpoints = $configInfo['breakpoints'][$columnType];
            }
        }

        return $breakpoints;
    }

    /**
     * @param $view
     * @param $templatePath
     *
     * @return bool
     */
    public function templateExists($view, $templatePath)
    {
        if (empty($templatePath)) {
            return FALSE;
        }

        $found = FALSE;
        $paths = $view->getScriptPaths();

        foreach ($paths as $path) {
            $p = $path . $templatePath;
            if (is_file($p)) {
                $found = TRUE;
            }
        }

        return $found;
    }

    /**
     * @param \Pimcore\Model\Asset $download
     * @param bool                 $showPreviewImage
     * @param bool                 $showFileInfo
     * @param string               $fileSizeUnit
     *
     * @return array
     */
    public function getDownloadInfo($download, $showPreviewImage = FALSE, $showFileInfo = FALSE, $fileSizeUnit = 'mb')
    {
        $hasMembers = ExtensionManager::isEnabled('plugin', 'Members');
        if ($hasMembers === TRUE && strpos($download->getFullPath(), \Members\Tool\UrlServant::PROTECTED_ASSET_FOLDER) !== FALSE) {
            $dPath = \Members\Tool\UrlServant::generateAssetUrl($download);
        } else {
            $dPath = $download->getFullPath();
        }

        $dSize = $download->getFileSize($fileSizeUnit, 2);
        $dType = \Pimcore\File::getFileExtension($download->getFilename());
        $dName = ($download->getMetadata('title')) ? $download->getMetadata('title') : $this->view->translate('Download');
        $dAltText = $download->getMetadata('alt') ? $download->getMetadata('alt') : $dName;
        $dPreviewImage = NULL;

        if ($showPreviewImage) {
            $dPreviewImage = $download->getMetadata('previewImage') instanceof \Pimcore\Model\Asset\Image
                ? $download->getMetadata('previewImage')->getThumbnail('downloadPreviewImage')
                : (
                $download instanceof \Pimcore\Model\Asset\Image
                    ? $download->getThumbnail('downloadPreviewImage')
                    : $download->getImageThumbnail('downloadPreviewImage')
                );
        }

        return [
            'path'         => $dPath,
            'size'         => $dSize,
            'type'         => $dType,
            'name'         => $dName,
            'altText'      => $dAltText,
            'previewImage' => $dPreviewImage
        ];
    }

}