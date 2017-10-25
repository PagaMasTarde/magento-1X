<?php


class Aplazame_Aplazame_Model_CampaignsObserver extends Mage_Core_Model_Abstract
{
    /**
     * @param Varien_Event_Observer $observer
     */
    public function campaignsMassActions($observer)
    {
        $block = $observer->getEvent()->getBlock();
        if (!($block instanceof Mage_Adminhtml_Block_Widget_Grid_Massaction)) {
            return;
        }

        if ($block->getRequest()->getControllerName() !== 'catalog_product') {
            return;
        }

        $helper = Mage::helper('aplazame');
        /** @var Mage_Adminhtml_Model_Url $adminUrl */
        $adminUrl = Mage::getModel('adminhtml/url');

        $block->addItem(
            'associateProductsToAplazameCampaigns',
            array(
                'label' => $helper->__('Associate to Aplazame Campaigns'),
                'url' => $adminUrl->getUrl('adminhtml/aplazame/productsCampaigns'),
            )
        );
        $block->addItem(
            'removeProductsFromAplazameCampaigns',
            array(
                'label' => $helper->__('Remove from Aplazame Campaigns'),
                'url' => $adminUrl->getUrl('adminhtml/aplazame/productsCampaigns'),
            )
        );
    }

    /**
     * @param Varien_Event_Observer $observer
     */
    public function productCampaigns($observer)
    {
        $block = $observer->getEvent()->getBlock();
        if (!($block instanceof Mage_Adminhtml_Block_Catalog_Product_Edit_Tabs)) {
            return;
        }

        /** @var Mage_Adminhtml_Block_Catalog_Product $product */
        $product = Mage::registry('product');
        if (!$this->_canAddTab($product)) {
            return;
        }

        $helper = Mage::helper('aplazame');
        /** @var Mage_Adminhtml_Model_Url $adminUrl */
        $adminUrl = Mage::getModel('adminhtml/url');

        $block->addTab(
            'aplazameCampaigns',
            array(
                'label' => $helper->__('Aplazame Campaigns'),
                'url' => $adminUrl->getUrl(
                    'adminhtml/aplazame/productCampaigns',
                    array('id' => $product->getId())
                ),
                'class' => 'ajax',
            )
        );
    }

    /**
     * @param Mage_Adminhtml_Block_Catalog_Product $product
     * @return bool
     */
    protected function _canAddTab($product)
    {
        if ($product->getId()) {
            return true;
        }

        if (!$product->getAttributeSetId()) {
            return false;
        }

        $request = Mage::app()->getRequest();
        if ($request->getParam('type') == 'configurable') {
            if ($request->getParam('attributes')) {
                return true;
            }
        }

        return false;
    }
}
