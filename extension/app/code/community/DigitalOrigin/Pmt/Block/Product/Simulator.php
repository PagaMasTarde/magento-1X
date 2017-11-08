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
        $isProduction = $config['PAYLATER_PROD'];
        $publicKey = $isProduction ? $config['PAYLATER_PUBLIC_KEY_PROD'] : $config['PAYLATER_PUBLIC_KEY_TEST'];
        $simulatorType = $config['PAYLATER_PRODUCT_HOOK_TYPE'];

        $this->assign(
            array(
                'amount' => 10,
                'publicKey' => $publicKey,
                'simulatorType' => $simulatorType
            )
        );

        return parent::_construct();
    }

    /**
     * Devuelve el current product cuando estamos en ficha de producto
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
     */
    public function getFinalPrice()
    {
        return Mage::app()->getStore()->convertPrice($this->getProduct()->getFinalPrice());
    }
}
