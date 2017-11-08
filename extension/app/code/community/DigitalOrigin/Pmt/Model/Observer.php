<?php

/**
 * Class DigitalOrigin_Pmt_Model_Observer
 */
class DigitalOrigin_Pmt_Model_Observer
{

    const AUTOLOADER_FILE = '/DigitalOrigin/autoload.php';

    public function addAutoloader()
    {
        $baseDir = Mage::getBaseDir('lib');
        $autoloadPath = self::AUTOLOADER_FILE;
        $autoload = $baseDir.$autoloadPath;

        require_once $autoload;

        return $this;
    }
}
