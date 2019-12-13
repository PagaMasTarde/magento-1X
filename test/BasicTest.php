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
    const TITLE_16 = 'Home page';

    /**
     * String
     */
    const TITLE_19 = 'Madison Island';

    /**
     * String
     */
    const BACKOFFICE_TITLE = 'Log into Magento Admin Page';

    /**
     * testMagentoOpen
     */
    public function testMagentoOpen()
    {
        $this->webDriver->get($this->magentoUrl);

        $title = $this->version = "16" ? self::TITLE_16 : self::TITLE_19;
        $this->webDriver->wait(10, 500)->until(
            WebDriverExpectedCondition::titleContains(
                $title
            )
        );

        $this->assertEquals($title, $this->webDriver->getTitle());
        $this->quit();
    }

    /**
     * testBackofficeOpen
     */
    public function testBackofficeOpen()
    {
        $this->webDriver->get($this->magentoUrl.self::BACKOFFICE_FOLDER);

        $this->webDriver->wait(10, 500)->until(
            WebDriverExpectedCondition::titleContains(
                self::BACKOFFICE_TITLE
            )
        );

        $this->assertContains(self::BACKOFFICE_TITLE, $this->webDriver->getTitle());
        $this->quit();
    }
}
