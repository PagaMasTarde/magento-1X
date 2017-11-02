<?php

/**
 * Class DigitalOrigin_Pmt_Model_Iframe
 */
class DigitalOrigin_Pmt_Model_CheckoutSimulatorType
{
    /**
     * NO
     */
    const NO = 0;

    /**
     * MINI
     */
    const MINI = 1;

    /**
     * COMPLETE
     */
    const COMPLETE = 2;

    /**
     * SELECTOR
     */
    const SELECTOR = 3;

    /**
     * TEXT
     */
    const TEXT = 4;

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array(
                'label' => Mage::helper('pmt')->__(' Mini'),
                'value' => self::MINI,
            ),
            array(
                'label' => Mage::helper('pmt')->__(' Complete'),
                'value' => self::COMPLETE,
            ),
            array(
                'label' => Mage::helper('pmt')->__(' Selector'),
                'value' => self::SELECTOR,
            ),
            array(
                'label' => Mage::helper('pmt')->__(' Descriptive Text'),
                'value' => self::TEXT,
            ),
            array(
                'label' => Mage::helper('pmt')->__(' Do not show'),
                'value' => self::NO,
            )
        );
    }
}
