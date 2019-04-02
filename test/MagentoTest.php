<?php

namespace Test;

use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Faker\Factory;
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
        'username'           => 'demo@prestashop.com',
        'password'           => 'prestashop_demo',
        'publicKey'          => 'tk_4954690ee76a4ff7875b93b4',
        'secretKey'          => '4392a844f7904be3',
        'birthdate'          => '05/05/2005',
        'firstname'          => 'Jøhn',
        'lastname'           => 'Dōè Martínez',
        'email'              => 'john_mg@digitalorigin.com',
        'company'            => 'Digital Origin SL',
        'zip'                => '08023',
        'country'            => 'España',
        'city'               => 'Barcelona',
        'street'             => 'Av Diagonal 585, planta 7',
        'phone'              => '600123123',
        'dni'                => '09422447Z',
        'defInstallments'    => '3',
        'maxInstallments'    => '12',
        'minAmount'          => '1'
    );

    /**
     * MagentoTest constructor.
     *
     * @param null   $name
     * @param array  $data
     * @param string $dataName
     */
    public function __construct($name = null, array $data = array(), $dataName = '')
    {
        $faker = Factory::create();

        $this->configuration['dni'] = $this->getDNI();
        $this->configuration['birthdate'] =
            $faker->numberBetween(1, 28) . '/' .
            $faker->numberBetween(1, 12). '/1975'
        ;
        $this->configuration['firstname'] = $faker->firstName;
        $this->configuration['lastname'] = $faker->lastName . ' ' . $faker->lastName;
        $this->configuration['company'] = $faker->company;
        $this->configuration['zip'] = $faker->postcode;
        $this->configuration['street'] = $faker->streetAddress;
        $this->configuration['phone'] = '6' . $faker->randomNumber(8);
        $this->configuration['email'] = date('ymd') . '@pagamastarde.com';

        parent::__construct($name, $data, $dataName);
    }

    /**
     * @return string
     */
    protected function getDNI()
    {
        $dni = '0000' . rand(pow(10, 4-1), pow(10, 4)-1);
        $value = (int) ($dni / 23);
        $value *= 23;
        $value= $dni - $value;
        $letter= "TRWAGMYFPDXBNJZSQVHLCKEO";
        $dniLetter= substr($letter, $value, 1);
        return $dni.$dniLetter;
    }

    /**
     * @var RemoteWebDriver
     */
    protected $webDriver;

    /**
     * Configure selenium
     */
    protected function setUp()
    {
        $this->webDriver = PmtWebDriver::create(
            'http://magento19-test.docker:4444/wd/hub',
            DesiredCapabilities::chrome(),
            120000,
            120000
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
