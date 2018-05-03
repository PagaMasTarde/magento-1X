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
        $checkoutSession = Mage::getModel('checkout/session');
        $quote = $checkoutSession->getQuote();
        $config = Mage::getStoreConfig('payment/paylater');
        $amount=$quote->getGrandTotal();
        $isProduction = $config['PAYLATER_PROD'];
        $publicKey = $isProduction ? $config['PAYLATER_PUBLIC_KEY_PROD'] : $config['PAYLATER_PUBLIC_KEY_TEST'];
        $simulatorType = $config['PAYLATER_CHECKOUT_HOOK_TYPE'];

        $logoTemplateClass = Mage::getConfig()->getBlockClassName('core/template');
        $logoTemplate = new $logoTemplateClass;
        $logoTemplate->setTemplate('pmt/checkout/logo.phtml');
        $logoHtml = $logoTemplate->toHtml();

        $this->assign(
            array(
                'amount' => $amount,
                'publicKey' => $publicKey,
                'simulatorType' => $simulatorType
            )
        );

        $title = $config['TITLE_EXTRA'];
        if (empty($title)) {
            $title = $config['title'];
        }

        $template = $this->setTemplate('pmt/checkout/paylater.phtml');
        $template
            ->setMethodTitle($title)
            ->setMethodLabelAfterHtml($logoHtml);
        ;

        parent::_construct();
    }
}
