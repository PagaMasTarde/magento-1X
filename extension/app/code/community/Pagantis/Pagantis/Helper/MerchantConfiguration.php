<?php

require_once(__DIR__.'/../../../../../../lib/Pagantis/autoload.php');

/**
 * Class Pagantis_Pagantis_Helper_MerchantConfiguration
 */
class Pagantis_Pagantis_Helper_MerchantConfiguration extends Mage_Core_Helper_Abstract
{
    /**
     * DEFAULT MIN AMOUNT
     */
    const DEF_MIN_AMOUNT = 0.00;

    /**
     * DEFAULT MAX AMOUNT
     */
    const DEF_MAX_AMOUNT = 0.00;

    /**
     * @var mixed|null $merchantConfig
     */
    protected $merchantConfig;

    /**
     * @var array|null $moduleConfig
     */
    protected $moduleConfig;

    /**
     * Default available countries for the different operational regions
     *
     * @var array
     */
    protected $defaultCountriesPerRegion = array(
        'ES' => '["ES", "FR", "IT"]',
        'GB' => '["GB"]',
        'US' => '["US"]'
    );


    /**
     * MerchantConfiguration constructor.
     */
    public function __construct()
    {
        $this->moduleConfig = $this->getModuleConfig();
        $this->merchantConfig = $this->getMerchantConfiguration();
    }

    /**
     * @return mixed|null
     * @throws \Afterpay\SDK\Exception\InvalidArgumentException
     * @throws \Afterpay\SDK\Exception\NetworkException
     * @throws \Afterpay\SDK\Exception\ParsingException
     */
    private function getMerchantConfiguration()
    {
        $configurationResponse = null;
        $language = substr(Mage::app()->getLocale()->getLocaleCode(), -2, 2);
        if ($this->moduleConfig['active'] &&
            !empty($this->moduleConfig['pagantis_merchant_id']) &&
            !empty($this->moduleConfig['pagantis_secret_key']) &&
            !empty($this->moduleConfig['pagantis_environment']) &&
            !empty($language)
        ) {
            if (!empty($this->moduleConfig['pagantis_merchant_id'])
                && !empty($this->moduleConfig['pagantis_secret_key'])
                && $this->moduleConfig['active']
            ) {
                $merchantAccount = new Afterpay\SDK\MerchantAccount();
                $merchantAccount
                    ->setMerchantId($this->moduleConfig['pagantis_merchant_id'])
                    ->setSecretKey($this->moduleConfig['pagantis_secret_key'])
                    ->setApiEnvironment($this->moduleConfig['pagantis_environment'])
                    ->setCountryCode($this->moduleConfig['pagantis_api_region']);

                $getConfigurationRequest = new Afterpay\SDK\HTTP\Request\GetConfiguration();
                $getConfigurationRequest->setMerchantAccount($merchantAccount);
                $getConfigurationRequest->send();
                $configurationResponse = $getConfigurationRequest->getResponse()->getParsedBody();
            }
        }

        // Update the allowed countries each time the config is required
        if (isset($configurationResponse[0]->activeCountries)) {
            Mage::helper('pagantis/ExtraConfig')->setExtraConfig(
                'ALLOWED_COUNTRIES',
                json_encode($configurationResponse[0]->activeCountries)
            );
        } else {
            $region = $this->moduleConfig['pagantis_api_region'];
            if (!empty($region) and is_string($region) && $region) {
                Mage::helper('pagantis/ExtraConfig')->setExtraConfig(
                    'ALLOWED_COUNTRIES',
                    $this->getCountriesPerRegion($region)
                );
            }
        }

        if (is_array($configurationResponse)) {
            return array_shift($configurationResponse);
        } else {
            return $configurationResponse;
        }
    }

    /**
     * @return mixed
     */
    private function getModuleConfig()
    {
        return Mage::getStoreConfig('payment/pagantis');
    }

    /**
     * @return float
     */
    public function getMinAmount()
    {
        if ($this->merchantConfig!=null && isset($this->merchantConfig->minimumAmount)) {
            return $this->merchantConfig->minimumAmount->amount;
        }

        if (isset($this->moduleConfig['pagantis_min_amount']) &&
            $this->moduleConfig['pagantis_min_amount'] != self::DEF_MAX_AMOUNT
        ) {
            return $this->moduleConfig['pagantis_min_amount'];
        }
        return self::DEF_MIN_AMOUNT;
    }

    /**
     * @return float
     */
    public function getMaxAmount()
    {
        if ($this->merchantConfig!=null && isset($this->merchantConfig->maximumAmount)) {
            return $this->merchantConfig->maximumAmount->amount;
        }

        if (isset($this->moduleConfig['pagantis_max_amount']) &&
            $this->moduleConfig['pagantis_max_amount'] != self::DEF_MAX_AMOUNT
        ) {
            return $this->moduleConfig['pagantis_max_amount'];
        }
        return self::DEF_MAX_AMOUNT;
    }

    /**
     * @param string $region
     * @return string
     */
    public function getCountriesPerRegion($region = '')
    {
        if (isset($this->defaultCountriesPerRegion[$region])) {
            return $this->defaultCountriesPerRegion[$region];
        }
        return json_encode(array());
    }
}
