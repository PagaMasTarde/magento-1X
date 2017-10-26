<?php

/**
 * Class DigitalOrigin_Pmt_Model_Iframe
 */
class DigitalOrigin_Pmt_Model_Iframe
{
    const IFRAME = 1;
    const REDIRECT = 0;

    public function toOptionArray()
    {
        return array(
            array(
                'label' => Mage::helper('pmt')->__(' Iframe'),
                'value' => self::IFRAME,
            ),
            array(
                'label' => Mage::helper('pmt')->__(' Redirect'),
                'value' => self::REDIRECT,
            )
        );
    }
}
