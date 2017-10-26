<?php

/**
 * Class DigitalOrigin_Pmt_Model_Iframe
 */
class DigitalOrigin_Pmt_Model_SimulatorType
{
    const MINI = 1;
    const COMPLETE = 2;
    const SELECTOR = 3;
    const TEXT = 4;

    public function toOptionArray()
    {
        return array(
            array(
                'label' => ' Mini',
                'value' => self::MINI,
            ),
            array(
                'label' => ' Complete',
                'value' => self::COMPLETE,
            ),
            array(
                'label' => ' Selector',
                'value' => self::SELECTOR,
            ),
            array(
                'label' => ' Descriptive Text',
                'value' => self::TEXT,
            )
        );
    }
}
