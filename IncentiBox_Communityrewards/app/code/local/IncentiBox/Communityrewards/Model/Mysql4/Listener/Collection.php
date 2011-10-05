<?php

class IncentiBox_Communityrewards_Model_Mysql4_Listener_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    /**
     * Declare base table and mapping of some fields
     */
    protected function _construct(){
        $this->_init('communityrewards/listener');
        //$this->_map['fields']['store'] = 'store_table.store_id';
    }

    public function toOptionArray(){
        return $this->_toOptionArray('unique_id', 'unique_id');
    }
}