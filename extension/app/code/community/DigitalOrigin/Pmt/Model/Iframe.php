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
                'label' => ' Iframe',
                'value' => self::IFRAME,
            ),
            array(
                'label' => ' Redirect',
                'value' => self::REDIRECT,
            )
        );
    }
}
