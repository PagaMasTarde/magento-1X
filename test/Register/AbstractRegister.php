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
    const TITLE = 'Madison Island';

    /**
     * OpenMagento page
     */
    public function openMagento()
    {
        $this->webDriver->get(self::MAGENTO_URL);

        $this->webDriver->wait(10, 500)->until(
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
        $accountLinkSearch = $footerSearch->partialLinkText(strtoupper('My Account'));
        $accountLinkElement = $this->webDriver->findElement($accountLinkSearch);
        $accountLinkElement->click();
        $createAccountButton = WebDriverBy::partialLinkText(strtoupper('Create an Account'));
        $condition = WebDriverExpectedCondition::elementToBeClickable($createAccountButton);
        $this->webDriver->wait()->until($condition);
        $this->assertTrue((bool) $condition);
    }

    /**
     * Go To account create
     */
    public function goToAccountCreate()
    {
        $createAccountSearch = WebDriverBy::partialLinkText(strtoupper('Create an Account'));
        $createAccountElement = $this->webDriver->findElement($createAccountSearch);
        $createAccountElement->click();
        $firstNameSearch = WebDriverBy::id('firstname');
        $condition = WebDriverExpectedCondition::presenceOfElementLocated($firstNameSearch);
        $this->webDriver->wait()->until($condition);
        $this->assertTrue((bool) $condition);
    }
}
