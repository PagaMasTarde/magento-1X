<?php

/**
 * Class DigitalOrigin_Pmt_Model_Enabled
 */
class DigitalOrigin_Pmt_Model_Enabled
{
    /**
     * PRODUCTION
     */
    const PRODUCTION = 1;

    /**
     * TESTING
     */
    const TESTING = 0;

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array(
                'label' => Mage::helper('pmt')->__(' Production'),
                'value' => self::PRODUCTION,
            ),
            array(
                'label' => Mage::helper('pmt')->__(' Testing'),
                'value' => self::TESTING,
            )
        );
    }
}
