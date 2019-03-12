<?php

/**
 * Class DigitalOrigin_Pmt_Model_Resource_Concurrency_Collection
 */
class DigitalOrigin_Pmt_Model_Resource_Concurrency_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    /**
     * Constructor
     */
    protected function _construct()
    {
        $this->_init('pmt/concurrency');
    }

    /**
     * Delete all items in the table
     *
     * @return DigitalOrigin_Pmt_Model_Resource_Concurrency_Collection
     */
    public function truncate()
    {
        foreach ($this->getItems() as $item) {
            var_dump($item->delete());
        }
        return $this;
    }
}
