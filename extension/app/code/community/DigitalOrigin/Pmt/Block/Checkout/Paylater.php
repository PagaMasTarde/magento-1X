<?php

/**
 * Class DigitalOrigin_Pmt_Block_Form_Paylater
 */
class DigitalOrigin_Pmt_Block_Checkout_Paylater extends Mage_Payment_Block_Form
{
    /**
     * Form constructor
     */
    protected function _construct()
    {
        /** @var Mage_Checkout_Model_Session $checkoutSession */
        $config = Mage::getStoreConfig('payment/paylater');
        $extraConfig = Mage::helper('pmt/ExtraConfig')->getExtraConfig();

        $this->assign(array(
            'pmtIsEnabled' => $config['active'],
        ));

        $title = $extraConfig['PMT_TITLE'];

        $classCoreTemplate = Mage::getConfig()->getBlockClassName('core/template');

        $logoHtml = '';
        if ($config['active']) {
            $logoTemplate = new $classCoreTemplate;
            $logoHtml = $logoTemplate->setTemplate('pmt/checkout/logo.phtml')->toHtml();
        }

        $template = $this->setTemplate('pmt/checkout/paylater.phtml');
        $template->setMethodTitle($title)->setMethodLabelAfterHtml($logoHtml);

        parent::_construct();
    }
}
