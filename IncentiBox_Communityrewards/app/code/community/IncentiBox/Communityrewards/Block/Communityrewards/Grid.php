<?php

class IncentiBox_Communityrewards_Block_Communityrewards_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('communityrewardsGrid');
        $this->setDefaultSort('unique_id');
        $this->setDefaultDir('ASC');
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('communityrewards/listener')->getCollection();
        /* @var $collection Mage_Cms_Model_Mysql4_Block_Collection */
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $baseUrl = $this->getUrl();

		$this->addColumn('unique_id', array(
            'header'    => Mage::helper('communityrewards')->__('ID'),
            'align'     => 'left',
            'index'     => 'unique_id'
        ));   
		$this->addColumn('incentibox_coupon_id', array(
					'header'    => Mage::helper('communityrewards')->__('Incentibox Coupon Id'),
					'align'     => 'left',
					'index'     => 'incentibox_coupon_id'
				)); 
		$this->addColumn('coupon_code', array(
					'header'    => Mage::helper('communityrewards')->__('Coupon Code'),
					'align'     => 'left',
					'index'     => 'coupon_code'
				)); 
		$this->addColumn('emailed_to', array(
					'header'    => Mage::helper('communityrewards')->__('Emailed To'),
					'align'     => 'left',
					'index'     => 'emailed_to'
				));
				
        return parent::_prepareColumns();
    }
	
    /**
     * Row click url
     *
     * @return string
     */
    public function getRowUrl($row)
    {
        return "#";
    }
}