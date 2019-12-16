<?php

namespace Test\Register;

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Test\MagentoTest;

/**
 * Class AbstractRegister
 * @package Test\Register
 */
abstract class AbstractRegister extends MagentoTest
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
     * OpenMagento page
     */
    public function openMagento()
    {
        $this->webDriver->get($this->magentoUrl);
        $title = $this->version = '16' ? self::TITLE_16 : self::TITLE_19;

        $this->webDriver->wait(10, 500)->until(
            WebDriverExpectedCondition::titleContains(
                $title
            )
        );

        $this->assertEquals($title, $this->webDriver->getTitle());
    }

    /**
     * Go to my account page
     */
    public function goToAccountPage()
    {
        $footerSearch = WebDriverBy::className('footer');
        $linkText = $title = $this->version = '16' ? 'My Account' : strtoupper('My Account');
        $accountLinkSearch = $footerSearch->partialLinkText($linkText);
        $accountLinkElement = $this->webDriver->findElement($accountLinkSearch);
        $accountLinkElement->click();
        if ($this->version = '16') {
            $createAccountButton = WebDriverBy::cssSelector('.new-users .button');
            $condition = WebDriverExpectedCondition::elementToBeClickable($createAccountButton);
            $this->webDriver->wait()->until($condition);
            $this->assertTrue((bool) $condition);
            $createAccountElement = $this->webDriver->findElement($createAccountButton);
            $createAccountElement->click();
            return;
        }
        $createAccountButton = WebDriverBy::partialLinkText(strtoupper('Create an Account'));
        $condition = WebDriverExpectedCondition::elementToBeClickable($createAccountButton);
        $this->webDriver->wait()->until($condition);
        $this->assertTrue((bool) $condition);
        $createAccountElement = $this->webDriver->findElement($createAccountButton);
        $createAccountElement->click();
    }

    /**
     * Go To account create
     */
    public function goToAccountCreate()
    {
        $firstNameSearch = WebDriverBy::id('firstname');
        $condition = WebDriverExpectedCondition::presenceOfElementLocated($firstNameSearch);
        $this->webDriver->wait()->until($condition);
        $this->assertTrue((bool) $condition);
    }
}
