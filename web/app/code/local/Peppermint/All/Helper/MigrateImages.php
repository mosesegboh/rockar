<?php
/**
 * @category  Peppermint
 * @package   Peppermint_All
 * @author    Mariam Khelashvili <techteam@rockar.com>
 * @copyright Copyright (c) 2021 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_All_Helper_MigrateImages extends Mage_Core_Helper_Abstract
{
    /**
     * @var null|Varien_Io_File $_ioObject
     */
    protected $_ioObject = null;

    /**
     * @var null|string $_destinationMediaDirectory
     */
    protected $_destinationMediaDirectory = null;

    /**
     * Copies an array of files from a source to a destination media directory.
     *
     * @param array $files
     * @param string $package
     * @param string $theme
     * @param string $folderPath
     * @param string $sourceSubfolder
     *
     * @return void
     */
    public function copyMediaFiles($files, $package, $theme, $folderPath = null, $sourceSubfolder = 'temp_media' . DS)
    {
        $sourceDirectory = $this->_getSourceMediaDirectory($package, $theme, $sourceSubfolder);
        $destDirectory = $this->_getDestinationMediaDirectory($folderPath);
        $this->_destinationMediaDirectory = $destDirectory;

        foreach ($files as $file) {
            $subfolderDestination = $this->_destinationMediaDirectory;
            $sourceFile = $sourceDirectory . $file;
            $pathWithoutFile = $this->_getArrayWithoutLastElement(explode('/', $file));

            /**
             * create necessary subfolders if these are missing
             */
            foreach ($pathWithoutFile as $subFolder) {
                $subfolderDestination .= $subFolder . DS;
                $this->_getIoObject()->checkAndCreateFolder($subfolderDestination);
            }

            /**
             * copy images into media folder
             */
            if ($this->_getIoObject()->fileExists($sourceFile)) {
                $this->_getIoObject()->cp($sourceFile, $destDirectory . $file);
            }
        }
    }

    /**
     * Gets the directory from which media files are copied.
     *
     * @param string $package
     * @param string $theme
     *
     * @return string
     */
    protected function _getSourceMediaDirectory($package, $theme, $sourceSubfolder)
    {
        $themePath = 'base' . DS . 'default';

        if ($package && $theme) {
            $themePath = $package . DS . $theme;
        }

        return Mage::getBaseDir('skin') . DS . 'frontend' . DS . $themePath . DS . 'images' . DS . $sourceSubfolder;
    }

    /**
     * Gets the directory in which media files are copied to.
     *
     * @param string $folderPath
     *
     * @return string
     */
    protected function _getDestinationMediaDirectory($folderPath = 'wysiwyg')
    {
        if (!$folderPath) {
            return Mage::getBaseDir('media') . DS;
        }

        return Mage::getBaseDir('media') . DS . $folderPath . DS;
    }

    /**
     * @param $array
     *
     * @return mixed
     */
    protected function _getArrayWithoutLastElement($array)
    {
        $arrayElementsCount = count($array);
        unset($array[$arrayElementsCount - 1]);

        return $array;
    }

    /**
     * Creates the IO object and optionally creates the directory.
     *
     * @return Varien_Io_File
     */
    protected function _getIoObject()
    {
        if ($this->_ioObject === null) {
            $this->_ioObject = new Varien_Io_File();
            $destDirectory = $this->_destinationMediaDirectory;

            try {
                $this->_ioObject->open(['path' => $destDirectory]);
            } catch (Exception $e) {
                $this->_ioObject->mkdir($destDirectory, 0777, true);
                $this->_ioObject->open(['path' => $destDirectory]);
            }
        }

        return $this->_ioObject;
    }
}
