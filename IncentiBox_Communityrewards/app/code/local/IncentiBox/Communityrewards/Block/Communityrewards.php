<?php

class IncentiBox_Communityrewards_Block_Communityrewards extends Mage_Adminhtml_Block_Widget_Grid_Container
{

    public function __construct()
    {
        $this->_controller = 'communityrewards';
		$this->_blockGroup = 'communityrewards';
		//$this->_controller = 'cmsgallery_cmsgallery';
        $this->_headerText = Mage::helper('communityrewards')->__('incentiBox Coupons');
        parent::__construct();
		$this->_removeButton('add'); 		
    }
}