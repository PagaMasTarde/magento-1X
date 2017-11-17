<?php

namespace Test;

use Facebook\WebDriver\WebDriverExpectedCondition;

/**
 * Class BasicTest
 * @package Test
 *
 * @group magento-basic
 */
class BasicTest extends MagentoTest
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
        $this->webDriver->get(self::MAGENTO_URL);

        $this->webDriver->wait(10, 500)->until(
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
        $this->webDriver->get(self::MAGENTO_URL.self::BACKOFFICE_FOLDER);

        $this->webDriver->wait(10, 500)->until(
            WebDriverExpectedCondition::titleContains(
                self::BACKOFFICE_TITLE
            )
        );

        $this->assertContains(self::BACKOFFICE_TITLE, $this->webDriver->getTitle());
        $this->quit();
    }
}
