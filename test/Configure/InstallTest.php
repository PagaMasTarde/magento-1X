<?php

namespace Test\Configure;

use Facebook\WebDriver\Exception\StaleElementReferenceException;
use Facebook\WebDriver\Remote\LocalFileDetector;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;

/**
 * Class InstallTest
 * @package Test\Configure
 *
 * @group magento-install
 */
class InstallTest extends AbstractConfigure
{
    /**
     * testMagentoModuleInstall
     */
    public function testMagentoModuleInstall()
    {
        $this->getBackofficeLoggedIn();
        $this->getToModuleAdd();
        $this->addModuleAndInstall();
        $this->webDriver->quit();
    }

    /**
     * Add Module And install
     */
    public function addModuleAndInstall()
    {
        // Push latest version of module form extension/var/connect
        $fileInput = $this->findById('file');
        $fileInput->setFileDetector(new LocalFileDetector());
        $latestVersion = 'DigitalOrigin_Pmt.tgz';

        $pageSource = $this->webDriver->getPageSource();

        //Check module not installed 2 times
        if (strpos($pageSource, 'DigitalOrigin')) {
            $this->assertContains(
                'DigitalOrigin',
                $pageSource
            );

            return true;
        } else {
            $file = __DIR__.'/../../extension/var/connect/'. $latestVersion;

            //Check file exists
            $this->assertFileExists($file);
            $fileInput->sendKeys($file);

            // Submit and verify
            $fileInput->submit();

            sleep(20);

            var_dump($pageSource = $this->webDriver->getPageSource());
            
            //Verify
            $successMessageSearch = WebDriverBy::className('success-msg');
            $this->webDriver->wait()->until(
                WebDriverExpectedCondition::visibilityOfElementLocated($successMessageSearch)
            );
            $this->assertTrue(
                (bool) WebDriverExpectedCondition::visibilityOfElementLocated($successMessageSearch)
            );
        }
    }
}
