<?php

/**
 * Class DigitalOrigin_Pmt_Model_Iframe
 */
class DigitalOrigin_Pmt_Model_ProductSimulator
{
    const YES = 1;
    const NO = 0;

    public function toOptionArray()
    {
        return array(
            array(
                'label' => ' Yes',
                'value' => self::YES,
            ),
            array(
                'label' => ' No',
                'value' => self::NO,
            )
        );
    }
}
