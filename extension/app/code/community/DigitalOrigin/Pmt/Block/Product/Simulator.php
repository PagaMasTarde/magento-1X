<?php

/**
 * Class DigitalOrigin_Pmt_Block_Product_Simulator
 */
class DigitalOrigin_Pmt_Block_Product_Simulator extends Mage_Catalog_Block_Product_View
{
    const PROMOTIONS_CATEGORY = 'paylater-promotion-product';

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
        $enabled = $config['active'];
        $promotionProductExtra = $config['PAYLATER_PROMOTION_EXTRA'];

        $this->assign(
            array(
                'enabled' => $enabled,
                'amount' => 10,
                'publicKey' => $publicKey,
                'simulatorType' => $simulatorType,
                'promotionProductExtra' => $promotionProductExtra,
            )
        );

        parent::_construct();
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
     * Is product in promotion
     *
     * @return bool
     */
    public function isProductInPromotion()
    {
        $categoryIds = $this->getProduct()->getCategoryIds();
        foreach ($categoryIds as $categoryId) {
            /** @var Mage_Catalog_Model_Category $category */
            $category = Mage::getModel('catalog/category')->load($categoryId);
            if ($category->getName() == self::PROMOTIONS_CATEGORY) {
                return true;
            }
        }

        return false;
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
