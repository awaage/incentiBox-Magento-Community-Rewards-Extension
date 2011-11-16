<?php
class IncentiBox_Communityrewards_Model_Mysql4_Listener extends Mage_Core_Model_Mysql4_Abstract
{
	function _construct(){
		$this->_init('communityrewards/listener','unique_id');
	}
}
?>