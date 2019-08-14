<?php
 /**
 * ZincPlatform
 * @package    Zinc_Carebyzinc
 * @copyright  Copyright (c) 2016-2017 Zinplatform (http://www.zincplatform.com/)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */ 
class Zinc_Carebyzinc_Model_Order extends Mage_Core_Model_Abstract
{

    const PRODUCTION    = 1;
    const SANDBOX   = 0;
    public function _construct()
    {
        parent::_construct();
        $this->_init('carebyzinc/order');
    }
	
	
	public function savePolicy($order)
	{
		$data = array();
		$currencyCode = Mage::app()->getStore()->getBaseCurrencyCode();
		$data['user_id'] =  $this->getUserId();
		$data['customer'] = $this->getUserAddress($order);
		$carebyItem = 0;$policyKeyArray = array();		
		$orderItems = $order->getAllVisibleItems();
		$fromname = Mage::getStoreConfig('trans_email/ident_general/name'); 
		$fromemail = Mage::getStoreConfig('trans_email/ident_general/email');
		$translate  = Mage::getSingleton('core/translate');
		$email = 'support@zincplatform.com';
		$name = 'Zinc';
		$templateId = Mage::getStoreConfig('sales_email/order/template');	
		
		$model = Mage::getModel('carebyzinc/carebyzinc');		
		if((Mage::getStoreConfig('carebyzinc/api/testmode')) == 'live')
			$mode = 1;
		else
			$mode = 0;
		foreach($orderItems as $item){
			if($item->getCarebyzincOption()){
				
				$policyNo = '';			
				$carebyzincAry = (array) unserialize($item->getCarebyzincOption()) ;
				$product = Mage::getModel('catalog/product')->load($item->getProductId());				
				$data['price_quote_id'] = $carebyzincAry['id'];				
				$data['sku_id'] = $carebyzincAry['sku_id'];				
				$result = $model->callApi($data, 'policies');
				$policyAry = $result['response'];
				if($result['code'] == 200){
					$policyArray   = (array) json_decode($policyAry);				
					$policyNo    =  $policyArray['policy_id'];
				}
				$policyKeyArray[] = $policyNo; 
				$carebyItem++;			
				$careOrder = Mage::getModel('carebyzinc/order');
				$careOrder->setOrderId($order->getId());
				$careOrder->setProductId($item->getProductId());
				$careOrder->setCustomerId($order->getCustomerId());
				$careOrder->setProductName($product->getName());
				$careOrder->setProductSku($product->getSku());
				$careOrder->setCarebyzincKey($policyNo);
				$careOrder->setOrderIncId($order->getIncrementId());
				$careOrder->setItemId($item->getId());
				$careOrder->setWarrentyPrice($carebyzincAry['price_per_year']);
				$price = $item->getPrice() -  $carebyzincAry['price_per_year'];
				$careOrder->setProductPrice($price);
				$careOrder->setCarebyzincProvider($carebyzincAry['provider']);
				$name = $order->getCustomerFirstname(). ' '. $order->getCustomerLastname();
				$careOrder->setCustomerName($name);
				$careOrder->setCustomerEmail($order->getCustomerEmail());
				$careOrder->setCreatedTime(now());
				$careOrder->setOrderCreatedMode($mode);
				$careOrder->save();
				
			}			
		}
		if($carebyItem){
			$fromname = Mage::getStoreConfig('trans_email/ident_general/name'); 
			$fromemail = Mage::getStoreConfig('trans_email/ident_general/email');
			$translate  = Mage::getSingleton('core/translate');
			$email = 'support@zincplatform.com';
			$name = 'Zinc';
			$templateId = Mage::getStoreConfig('sales_email/order/template');
			if($email){
				for($i =0;$i<count($policyKeyArray);$i++){
				        $storeObj = Mage::getModel('core/store')->load($order->getStoreId());
				        $anyDate = $order->getCreatedAt();
					$dateTimestamp = Mage::getModel('core/date')->timestamp(strtotime($anyDate));   
					$date = date("Y-m-d",$dateTimestamp);
					$subject = $this->getUserId().'_'.$policyKeyArray[$i].'_'.$storeObj->getFrontendName().'_'.$date;	  		
				        $emailTemplate = Mage::getModel('core/email_template')->loadDefault('sales_email_order_template');           
				        $emailTemplateVariables = array();
				        $emailTemplateVariables['order'] = $order;
				        $emailTemplateVariables['store'] = $storeObj;       
				        $emailTemplate->setSenderName($fromname);
				        $emailTemplate->setSenderEmail($fromemail);
				        $emailTemplate->setType('html');
				        $emailTemplate->setTemplateSubject($subject);
				        $emailTemplate->send($email, $name, $emailTemplateVariables);		
  			  	}		
	   			
	   		}  
		
		}
	}
	
	private function getUserId()
	{
		$userId = Mage::getStoreConfig('carebyzinc/api/user_id');
		return $userId;
	}
	
	public function getUserAddress($order)
	{
		$address ['first_name'] = $order->getCustomerFirstname();
		$address ['last_name'] = $order->getCustomerLastname();
		$address ['transaction_currency'] = $order->getOrderCurrencyCode();
		$address ['email'] = $order->getCustomerEmail();
		$billingAddress = $order->getBillingAddress();
		$address ['main_phone_number'] = $billingAddress->getTelephone();
		$address ['country'] = $billingAddress->getCountryId();
		$address ['address'] = array( 
								"billing_city" => $billingAddress->getCity(),
								"billing_zip_code" => $billingAddress->getPostcode(),
								"billing_state" => $billingAddress->getRegion(),
								"billing_address1" => $billingAddress->getStreet(),
							);
							
		return $address;
		
	}
	
   
 
	
   static public function getOptionArray()
    {
        return array(
            self::PRODUCTION    => Mage::helper('carebyzinc')->__('Production'),
            self::SANDBOX   => Mage::helper('carebyzinc')->__('Sandbox')
        );
    }	
}
