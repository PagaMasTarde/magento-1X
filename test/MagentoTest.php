<?php

namespace Test;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\WebDriverCapabilityType;
use Facebook\WebDriver\WebDriverBy;
use PHPUnit\Framework\TestCase;

/**
 * Class MagentoTest
 * @package Test
 */
abstract class MagentoTest extends TestCase
{
    const MAGENTO_URL = 'http://magento-test';
    const BACKOFFICE_FOLDER = '/index.php/admin';

    /**
     * @var array
     */
    protected $configuration = array(
        'username'      => 'demo@prestashop.com',
        'password'      => 'prestashop_demo',
        'publicKey'     => 'tk_fd53cd467ba49022e4f8215e',
        'secretKey'     => '21e57baa97459f6a',
        'birthdate'     => '05/05/2005',
        'firstname'     => 'John',
        'lastname'      => 'Doe',
        'email'         => 'john.doe@digitalorigin.com',
        'company'       => 'Digital Origin SL',
        'zip'           => '08023',
        'city'          => 'Barcelona',
        'phone'         => '600123123',
        'dni'           => '09422447Z'
    );

    /**
     * @var RemoteWebDriver
     */
    protected $webDriver;

    /**
     * Configure selenium
     */
    protected function setUp()
    {
        $capabilities = array(WebDriverCapabilityType::BROWSER_NAME => 'chrome');
        $this->webDriver = RemoteWebDriver::create('http://localhost:4444/wd/hub', $capabilities);
    }

    /**
     * @param $name
     *
     * @return \Facebook\WebDriver\Remote\RemoteWebElement
     */
    public function findByName($name)
    {
        return $this->webDriver->findElement(WebDriverBy::name($name));
    }

    /**
     * @param $id
     *
     * @return \Facebook\WebDriver\Remote\RemoteWebElement
     */
    public function findById($id)
    {
        return $this->webDriver->findElement(WebDriverBy::id($id));
    }

    /**
     * @param $className
     *
     * @return \Facebook\WebDriver\Remote\RemoteWebElement
     */
    public function findByClass($className)
    {
        return $this->webDriver->findElement(WebDriverBy::className($className));
    }

    /**
     * @param $css
     *
     * @return \Facebook\WebDriver\Remote\RemoteWebElement
     */
    public function findByCss($css)
    {
        return $this->webDriver->findElement(WebDriverBy::cssSelector($css));
    }

    /**
     * @param $link
     *
     * @return \Facebook\WebDriver\Remote\RemoteWebElement
     */
    public function findByLinkText($link)
    {
        return $this->webDriver->findElement(WebDriverBy::linkText($link));
    }

    /**
     * Quit browser
     */
    protected function quit()
    {
        $this->webDriver->quit();
    }
}
