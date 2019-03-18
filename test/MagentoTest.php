<?php

namespace Test;

use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use PHPUnit\Framework\TestCase;

/**
 * Class MagentoTest
 * @package Test
 */
abstract class MagentoTest extends TestCase
{
    /**
     * Magento URL
     */
    const MAGENTO_URL = 'http://magento19-test.docker:8082/index.php';

    /**
     * Magento Backoffice URL
     */
    const BACKOFFICE_FOLDER = '/admin';

    /**
     * @var array
     */
    protected $configuration = array(
        'backofficeUsername' => 'admin',
        'backofficePassword' => 'password123',
        'username'           => 'demo@magento.com',
        'password'           => 'mangento_demo',
        'publicKey'          => 'tk_cd552f3cbf23434fbb0c5dd1',
        'secretKey'          => 'f1fda1231c774c2b',
        'birthdate'          => '05/05/2005',
        'firstname'          => 'Péte®',
        'lastname'           => 'Köonsç Martínez',
        'email'              => 'john_mg@digitalorigin.com',
        'company'            => 'Pagantis SA',
        'zip'                => '08023',
        'country'            => 'España',
        'city'               => 'Barcelona',
        'street'             => 'Av Diagonal 485, planta 7',
        'phone'              => '600123123',
        'dni'                => '02180900V',
        'defInstallments'    => '3',
        'maxInstallments'    => '12',
        'minAmount'          => '1'
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
        $this->webDriver = PagantisWebDriver::create(
            'http://localhost:4444/wd/hub',
            DesiredCapabilities::chrome(),
            90000,
            90000
        );
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
     * @param $link
     *
     * @return \Facebook\WebDriver\Remote\RemoteWebElement
     */
    public function findByPartialLinkText($link)
    {
        return $this->webDriver->findElement(WebDriverBy::partialLinkText($link));
    }

    /**
     * @param $element
     *
     * @throws \Facebook\WebDriver\Exception\NoSuchElementException
     * @throws \Facebook\WebDriver\Exception\TimeOutException
     */
    public function clickElement($element)
    {
        $this->webDriver->wait()->until(
            WebDriverExpectedCondition::elementToBeClickable(
                $element
            )
        );
        $accountMenuElement = $this->webDriver->findElement($element);
        $accountMenuElement->click();
    }

    /**
     * Quit browser
     */
    public function quit()
    {
        $this->webDriver->quit();
    }
}
