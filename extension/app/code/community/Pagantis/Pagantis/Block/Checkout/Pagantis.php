<?php

/**
 * Class Pagantis_Pagantis_Block_Form_Pagantis
 */
class Pagantis_Pagantis_Block_Checkout_Pagantis extends Mage_Payment_Block_Form
{
    /**
     * Form constructor
     */
    protected function _construct()
    {
        /** @var Mage_Checkout_Model_Session $checkoutSession */
        $config = Mage::getStoreConfig('payment/pagantis');
        $extraConfig = Mage::helper('pagantis/ExtraConfig')->getExtraConfig();
        $locale = substr(Mage::app()->getLocale()->getLocaleCode(),-2,2);

        $this->assign(array(
            'locale'            => $locale,
            'pagantisIsEnabled' => $config['active'],
        ));

        $title = $this->__($extraConfig['PAGANTIS_TITLE']);

        $classCoreTemplate = Mage::getConfig()->getBlockClassName('core/template');

        $logoHtml = '';
        if ($config['active']) {
            $logoTemplate = new $classCoreTemplate;
            $logoTemplate->assign(array(
                'locale'            => $locale,
                'pagantisIsEnabled' => $config['active'],
            ));
            $logoHtml = $logoTemplate->setTemplate('pagantis/checkout/logo.phtml')->toHtml();

            if ($logoHtml == '') {
                $logoTemplate->_allowSymlinks = true;
                $logoHtml = $logoTemplate->setTemplate('pagantis/checkout/logo.phtml')->toHtml();
            }
        }

        $template = $this->setTemplate('pagantis/checkout/pagantis.phtml');

        if ($template->toHtml() == '') {
            $this->_allowSymlinks = true;
        }
        $template->setMethodTitle($title)->setMethodLabelAfterHtml($logoHtml);

        parent::_construct();
    }
}
