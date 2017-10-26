<?php

/**
 * Class DigitalOrigin_Pmt_Model_Enabled
 */
class DigitalOrigin_Pmt_Model_Enabled
{
    const PRODUCTION = 1;
    const TESTING = 0;

    public function toOptionArray()
    {
        return array(
            array(
                'label' => ' Production',
                'value' => self::PRODUCTION,
            ),
            array(
                'label' => ' Testing',
                'value' => self::TESTING,
            )
        );
    }
}
