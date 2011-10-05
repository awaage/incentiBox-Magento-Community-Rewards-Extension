<?php

class IncentiBox_Communityrewards_CommunityrewardsController extends Mage_Adminhtml_Controller_Action
{
    
    protected function _initAction()
    {
        // load layout, set active menu and breadcrumbs
        $this->loadLayout()
            ->_setActiveMenu('promo/communityrewards')
            ->_addBreadcrumb(Mage::helper('adminhtml')->__('Promo'), Mage::helper('adminhtml')->__('Promo'))
            ->_addBreadcrumb(Mage::helper('adminhtml')->__('Incentobox Coupons'), Mage::helper('adminhtml')->__('incentiBox Coupons'))
        ;
        return $this;
    }
	
	protected function isIncentoboxCouponActive(){
		return Mage::getStoreConfig("communityrewards/communityrewards_settings/active");
	}

    /**
     * Index action
     */
    public function indexAction()
    {
		if(!$this->isIncentoboxCouponActive()){
			Mage::getSingleton('core/session')->addError($this->__("EXTENSION DISABLED - Please enable the extension by going to the <a href='%s'>System > incentiBox Coupons</a> configuration page.", $this->getUrl('adminhtml/system_config/edit', array('section'=>'communityrewards'))));
		}
			
		$this->_title($this->__('Promo'))->_title($this->__('incentiBox Coupons'));

        $this->_initAction();
		$this->renderLayout();
    }
}
