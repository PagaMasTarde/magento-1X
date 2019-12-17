<?php

namespace Test;

use Facebook\WebDriver\WebDriverExpectedCondition;

/**
 * Class Basic19Test
 * @package Test
 *
 * @group magento-basic-19
 */
class Basic19Test extends MagentoTest
{
    /**
     * String
     */
    const TITLE = 'Madison Island';

    /**
     * String
     */
    const BACKOFFICE_TITLE = 'Log into Magento Admin Page';

    /**
     * testMagentoOpen
     */
    public function testMagentoOpen()
    {
        $this->webDriver->get($this->magentoUrl19);
        $this->webDriver->wait()->until(
            WebDriverExpectedCondition::titleContains(
                self::TITLE
            )
        );

        $this->assertEquals(self::TITLE, $this->webDriver->getTitle());
        $this->quit();
    }

    /**
     * testBackofficeOpen
     */
    public function testBackofficeOpen()
    {
        $this->webDriver->get($this->magentoUrl19.self::BACKOFFICE_FOLDER);
        $this->webDriver->wait()->until(
            WebDriverExpectedCondition::titleContains(
                self::BACKOFFICE_TITLE
            )
        );

        $this->assertContains(self::BACKOFFICE_TITLE, $this->webDriver->getTitle());
        $this->quit();
    }
}