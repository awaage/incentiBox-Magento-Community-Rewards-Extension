<?php
require_once(Mage::getBaseDir('lib').'/Incentibox/incentibox_api.php');
class IncentiBox_Communityrewards_ListenerController extends Mage_Core_Controller_Front_Action
{
	protected function isIncentoboxCouponActive(){
		return Mage::getStoreConfig("communityrewards/communityrewards_settings/active");
	}
		
	public function indexAction()
	{		
		if(!$this->isIncentoboxCouponActive()){
			header("HTTP/1.0 500 Internal Server Error");
			header("incentiBoxSuccess: FALSE");
			exit();
		}
		
		try
		{
			$VERBOSE = true;
			$params=$this->getRequest()->getParams();
			if (empty($params['ib_run']))
			{	
				header("HTTP/1.0 500 Internal Server Error");
				header("incentiBoxSuccess: FALSE");
				exit();
			}
			
			// get last coupon id
			$last_coupon_id = $this->getIncentiboxLastCouponId();
			
			$INCENTIBOX_API_USER= Mage::getStoreConfig('communityrewards/communityrewards_settings/incentibox_api_user');
			$INCENTIBOX_API_PASSWORD= Mage::getStoreConfig('communityrewards/communityrewards_settings/incentibox_api_password');
			$INCENTIBOX_PROGRAM_ID = Mage::getStoreConfig('communityrewards/communityrewards_settings/program_id');
			$expiration_days = Mage::getStoreConfig('communityrewards/communityrewards_settings/coupon_expires');
			
			if(!$expiration_days){
				$expiration_days = 7;
			}
			
			if(!$INCENTIBOX_API_USER){
				header("HTTP/1.0 500 Internal Server Error");
				header("incentiBoxSuccess: FALSE");
				exit();
			}
			if(!$INCENTIBOX_API_PASSWORD){
				header("HTTP/1.0 500 Internal Server Error");
				header("incentiBoxSuccess: FALSE");
				exit();
			}
			if(!$INCENTIBOX_PROGRAM_ID){
				header("HTTP/1.0 500 Internal Server Error");
				header("incentiBoxSuccess: FALSE");
				exit();
			}
			
			
			$incentibox_client = new IncentiboxApi($INCENTIBOX_API_USER,$INCENTIBOX_API_PASSWORD);
			// returns all the redeemed_rewards for this program
			$new_rewards_array = $incentibox_client->get_redeemed_rewards($INCENTIBOX_PROGRAM_ID, $last_coupon_id);
			
			// insert coupon in incentibox_coupon table and also in magento
			foreach ($new_rewards_array as $new_reward)
			{
				$listener = Mage::getModel('communityrewards/listener');
				$listener->setIncentiboxCouponId($new_reward['id']);
				$listener->setCouponCode($new_reward['code']);
				$listener->setCouponAmount($new_reward['amount']);
				$listener->setOrderMinimum($new_reward['order_minimum']);
				$listener->setDateRedeemed($new_reward['redeemed_at']);
				$listener->setEmailedTo($new_reward['email']);
				$listener->setDateCreated(now()); 	
				$listener->save();
				
				
				// now insert coupon in magento
				$model = Mage::getModel('salesrule/rule');
				$session = Mage::getSingleton('adminhtml/session');
				$toDate = date("F d, Y H:i:s", Mage::getModel('core/date')->timestamp(time()));
				$copunValidateDays = $expiration_days;
				$nextday=strftime("%Y-%m-%d", strtotime("$todate +$copunValidateDays day"));
					
				$data = array();
					$data['name'] = "incentiBox Coupon ". $listener->getIncentiboxCouponId();
					$data['coupon_type'] = '2';
					$data['coupon_code'] = $listener->getCouponCode();
					$data['is_active']= '1';
					$data['uses_per_coupon'] = '1';
					$data['uses_per_customer'] = '1';
					$data['to_date']= $nextday;
					$data['simple_action']='by_fixed';
					$data['discount_amount']=$listener->getCouponAmount();
					
					// get all websites
					$websites = Mage::getSingleton('adminhtml/system_config_source_website')->toOptionArray();
					$website_ids = array();
					if(count($websites)>0){
						foreach($websites as $website){
							$website_ids[] = $website['value'];
						}
					}
					$data['website_ids'] = $website_ids;
					
					// prepare customer group ids
					$customer_group_ids = array();
					$groups = Mage::getResourceModel('customer/group_collection')
						// ->addFieldToFilter('customer_group_id', array('gt'=> 0))
						->load()
						->toOptionArray();
					
					if(count($groups)>0){
						foreach($groups as $group){
							$customer_group_ids[] = $group['value'];
						}
					}
					$data['customer_group_ids'] = $customer_group_ids;
				
					// prepare rule
        			$rule=array(
						"conditions"=>array(
							"1"=>array(
								"type" => "salesrule/rule_condition_combine",
								"aggregator" => "all",
								"value" => "1",
								"new_child" => "",
								),
							"1--1"=>array(
									"type" => "salesrule/rule_condition_address",
									"attribute" => "base_subtotal",
									"operator" => ">=",
									"value" => $listener->getOrderMinimum(),
								)
						),
						"actions" =>array(
							"1" => Array(
								"type" => "salesrule/rule_condition_product_combine",
								"aggregator" => "all",
								"value" => "1",
								"new_child" =>"" 
								)
							)
						);
						$data['rule']=$rule;
							if (isset($data['rule']['conditions'])) {
								$data['conditions'] = $data['rule']['conditions'];
							}
							if (isset($data['rule']['actions'])) {
								$data['actions'] = $data['rule']['actions'];
							}
					
						unset($data['rule']);
				
				
				
				$model1=$model->loadPost($data);
				$session->setPageData($model->getData());
				$model->save();
			}
			header("HTTP/1.0 200 OK");
			header("incentiBoxSuccess: TRUE");
			exit;
		}
		catch (Mage_Core_Exception $e) 
		{
			
			// echo $e->getMessage();
			return;
        } 
		catch (Exception $e)
		{
			// echo $e->getMessage();
			return;
		}
    }
	
	public function getIncentiboxLastCouponId(){
		// get table name
		$coreResource = Mage::getSingleton('core/resource');
		$tableName = $coreResource->getTableName('incentibox_coupons');  
		
		//prepare query and run
		// fetch write database connection that is used in Mage_Core module
		$write = Mage::getSingleton('core/resource')->getConnection('core_write');
		$query  = "select incentibox_coupon_id from ".$tableName." order by incentibox_coupon_id DESC LIMIT 1";
		$data = $coreResource->getConnection('core_read')->fetchAll($query); 
		if(isset($data[0]['incentibox_coupon_id']) && !empty($data[0]['incentibox_coupon_id'])){
			return $data[0]['incentibox_coupon_id'];
		}
		return false;
	}
}

?>