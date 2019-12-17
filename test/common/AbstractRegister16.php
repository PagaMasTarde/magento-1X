<?php

namespace Test\Common;

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Test\MagentoTest;

/**
 * Class AbstractRegister16
 * @package Test\Register
 */
abstract class AbstractRegister16 extends MagentoTest
{
    /**
     * String
     */
    const TITLE = 'Home page';

    /**
     * OpenMagento page
     */
    public function openMagento()
    {
        $this->webDriver->get($this->magentoUrl16);
        $this->webDriver->wait()->until(
            WebDriverExpectedCondition::titleContains(
                self::TITLE
            )
        );

        $this->assertEquals(self::TITLE, $this->webDriver->getTitle());
    }

    /**
     * Go to my account page
     */
    public function goToAccountPage()
    {
        $footerSearch = WebDriverBy::className('footer');
        $linkText = $title = 'My Account';
        $accountLinkSearch = $footerSearch->partialLinkText($linkText);
        $accountLinkElement = $this->webDriver->findElement($accountLinkSearch);
        $accountLinkElement->click();
        $createAccountButton = WebDriverBy::cssSelector('.new-users .button');
        $condition = WebDriverExpectedCondition::elementToBeClickable($createAccountButton);
        $this->webDriver->wait()->until($condition);
        $this->assertTrue((bool) $condition);
    }

    /**
     * Go To account create
     */
    public function goToAccountCreate()
    {
        $createAccountButton = WebDriverBy::cssSelector('.new-users .button');
        $createAccountElement = $this->webDriver->findElement($createAccountButton);
        $createAccountElement->click();
        $firstNameSearch = WebDriverBy::id('firstname');
        $condition = WebDriverExpectedCondition::presenceOfElementLocated($firstNameSearch);
        $this->webDriver->wait()->until($condition);
        $this->assertTrue((bool) $condition);
    }
}
