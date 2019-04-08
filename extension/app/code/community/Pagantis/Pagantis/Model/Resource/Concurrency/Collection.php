<?php

/**
 * Class Pagantis_Pagantis_Model_Resource_Concurrency_Collection
 */
class Pagantis_Pagantis_Model_Resource_Concurrency_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    /**
     * Constructor
     */
    protected function _construct()
    {
        $this->_init('pagantis/concurrency');
    }

    /**
     * Delete all items in the table
     *
     * @return Pagantis_Pagantis_Model_Resource_Concurrency_Collection
     */
    public function truncate()
    {
        foreach ($this->getItems() as $item) {
            $item->delete();
        }
        return $this;
    }
}
