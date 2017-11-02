<?php

/**
 * Class DigitalOrigin_Pmt_Model_Iframe
 */
class DigitalOrigin_Pmt_Model_Iframe
{
    /**
     * IFRAME
     */
    const IFRAME = 1;

    /**
     * REDIRECT
     */
    const REDIRECT = 0;

    /**
     * @return array
     */
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
