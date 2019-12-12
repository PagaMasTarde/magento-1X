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
     * Magento TEST
     */
    const MAGENTO16_URL_TEST = 'http://magento16-test.docker:8017/index.php';

    /**
     * Magento TEST
     */
    const MAGENTO19_URL_TEST = 'http://magento19-test.docker:8082/index.php';

    /**
     * Magento DEV
     */
    const MAGENTO_URL_DEV = 'http://magento19-dev.docker:8080/index.php';

    /**
     * Magento Backoffice URL
     */
    const BACKOFFICE_FOLDER = '/admin';

    /**
     * @var string URL for test
     */
    public $magentoUrl;

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
        'email'              => 'john_mg@pagantis.com',
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
     * MagentoTest constructor.
     *
     * @param null   $name
     * @param array  $data
     * @param string $dataName
     */
    public function __construct($name = null, array $data = array(), $dataName = '')
    {
        $this->magentoUrl = $this->getMagentoUrl();
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
        $this->configuration['email'] = date('ymd') . '@pagantis.com';

        parent::__construct($name, $data, $dataName);
    }

    /**
     * getTestEnvironment
     */
    protected function getMagentoUrl()
    {
        $env = getenv('MAGENTO_TEST_ENV');

        if ($env == 'dev') {
            return self::MAGENTO_URL_DEV;
        }

        if (MG_VERSION == "16") {
            return self::MAGENTO16_URL_TEST;
        }
        return self::MAGENTO19_URL_TEST;
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
        $this->webDriver = PagantisWebDriver::create(
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
