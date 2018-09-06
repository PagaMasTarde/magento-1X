<?php

/**
 * Class DigitalOrigin_Pmt_Block_Product_Simulator
 */
class DigitalOrigin_Pmt_Block_Product_Simulator extends Mage_Catalog_Block_Product_View
{
    /**
     * @var Mage_Catalog_Model_Product $_product
     */
    protected $_product;

    /**
     * Form constructor
     */
    protected function _construct()
    {
        $config = Mage::getStoreConfig('payment/paylater');
        $this->assign(
            array(
                'enabled' => $config['active'],
                'publicKey' => ($config['PAYLATER_PROD']) ? $config['PAYLATER_PUBLIC_KEY_PROD'] : $config['PAYLATER_PUBLIC_KEY_TEST'],
                'simulatorType' => $config['PAYLATER_PRODUCT_HOOK_TYPE'],
                'defaultInstallments' => $config['DEFAULT_INSTALLMENTS'],
                'maxInstallments' => $config['MAX_INSTALLMENTS'],
                'minAmount' => $config['MIN_AMOUNT']
            )
        );

        parent::_construct();
    }

    /**
     * Devuelve el current product cuando estamos en ficha de producto
     *
     * @return Mage_Catalog_Model_Product|mixed
     */
    public function getProduct()
    {
        if (!$this->_product) {
            $this->_product = Mage::registry('current_product');
        }

        return $this->_product;
    }

    /**
     * @return float
     * @throws Mage_Core_Model_Store_Exception
     */
    public function getFinalPrice()
    {
        return Mage::app()->getStore()->convertPrice($this->getProduct()->getFinalPrice());
    }
}
