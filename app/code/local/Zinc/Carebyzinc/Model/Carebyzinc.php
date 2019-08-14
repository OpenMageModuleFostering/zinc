<?php
 /**
 * ZincPlatform
 * @package    Zinc_Carebyzinc
 * @copyright  Copyright (c) 2016-2017 Zinplatform (http://www.zincplatform.com/)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */ 
class Zinc_Carebyzinc_Model_Carebyzinc extends Mage_Core_Model_Abstract
{
   const WARRANTY_ENABLED    = 1;
   const WARRANTY_DISABLED   = 0;
   
 	 public function _construct()
    {
        parent::_construct();
        $this->_init('carebyzinc/carebyzinc');
    }
	
   static public function getOptionArray()
    {
        return array(
            self::WARRANTY_ENABLED    => Mage::helper('carebyzinc')->__('Enabled'),
            self::WARRANTY_DISABLED   => Mage::helper('carebyzinc')->__('Disabled')
        );
    }	
	

	 public function getPriceQuote($product,$zip,$optionPrice)
	{
	  $currencyCode = Mage::app()->getStore()->getBaseCurrencyCode();
	  $price = $product->getFinalPrice();
	  if($optionPrice)
	  	$price += $optionPrice;
	  $userId       = $this->getUserId();
	  $data = array();  
	  $data['sku'] = array(
	       'merchant_sku_id' => $product->getId(), 
	       'sku_name' => $product->getName(), 
	       'variant_id' => '', 
	       'category' => $product->getCarebyzincCategory(), 
	       'subcategory' => $product->getCarebyzincSubcategory(),  
	       'subcategory2' => '', 
	       'description' => $product->getShortDescription(), 
	       'price' => $price,
	       'currency' => $currencyCode, 
	       'user_id' => $userId
	      );
	  
	  if(! $zip){
		  $helper =  Mage::helper('carebyzinc');
		  $zip = $helper->getZipCode();
	  }
	  $data['zip_code'] = $zip;
	  $outData  = $this->callApi($data, 'price_quotes/generate');
	  if($outData['code'] == '200'){
	   	  $response = $outData['response'];
		  $quoteData = json_decode($response, true);
		  $priceQuote = array();  
		  foreach($quoteData['price_quotes'] as $item):
		   $priceQuote[$item['id']] = $item;
		  endforeach;   
		  Mage::getSingleton('core/session')->setCareQuote($priceQuote);
		  return $priceQuote;

	  }else{
	   	$response = $outData['response'];
		$response = (array)json_decode($response, true); 
	  	return $response['errors'];
	  }
     
 	}
 	
 	 public function getPriceQuoteinCart($product,$itemId,$zip)
	{
	
	  $currencyCode = Mage::app()->getStore()->getBaseCurrencyCode();
	  $_item = Mage::getModel('sales/quote_item')->load($itemId);
	  $userId       = $this->getUserId();
	  $data = array();  
	  $data['sku'] = array(
	       'merchant_sku_id' => $product->getId(), 
	       'sku_name' => $product->getName(), 
	       'variant_id' => '', 
	       'category' => $product->getCarebyzincCategory(),
	       'subcategory' => $product->getCarebyzincSubcategory(),  
	       'subcategory2' => '', 
	       'description' => $product->getShortDescription(), 
	       'price' => $_item->getPrice(), 
	       'currency' => $currencyCode, 
	       'user_id' => $userId
	      );
	  if(! $zip){
		  $helper =  Mage::helper('carebyzinc');
		  $zip = $helper->getZipCode();
	  }
	  $data['zip_code'] = $zip;
	  $outData = $this->callApi($data, 'price_quotes/generate');
	  if($outData['code'] == '200'){
		  $response = $outData['response'];
		  $quoteData = json_decode($response, true);
		  $priceQuote = array();	 
		  foreach($quoteData['price_quotes'] as $item):
				$priceQuote[$itemId][$item['id']] = $item;
		  endforeach;  
		  Mage::getSingleton('core/session')->setCareByZincQuote($priceQuote);
		  return $priceQuote;
	  }else{
	   	$response = $outData['response'];
		$response = (array)json_decode($response, true); 
	  	return $response['errors'];
	  }
     
 	}
	
	public function getApiUrl($action)
	{
		$path = Mage::getStoreConfig('carebyzinc/api/url');
		if((Mage::getStoreConfig('carebyzinc/api/testmode')) == 'live')
			$path = Mage::getStoreConfig('carebyzinc/api/url');
		else
			$path = Mage::getStoreConfig('carebyzinc/api/test_url');
		if(Mage::getStoreConfig('carebyzinc/api/protocol') == 'https')
			$protocol = 'https://'	;
		else
			$protocol = 'http://'	;
		$url = $protocol.$path.'/'.$action;
		return $url;
	}
	
	public function getUserId()
	{
		
		if((Mage::getStoreConfig('carebyzinc/api/testmode')) == 'live')
			$userId = Mage::getStoreConfig('carebyzinc/api/user_id');
		else
			$userId = Mage::getStoreConfig('carebyzinc/api/test_user_id');
		return $userId;
	}
	public function getToken()
	{
		
		if((Mage::getStoreConfig('carebyzinc/api/testmode')) == 'live')
			$token = Mage::getStoreConfig('carebyzinc/api/xuser_token');
		else
			$token = Mage::getStoreConfig('carebyzinc/api/test_xuser_token');
		return $token;
	}
	
	public function callApi($data, $action, $method = 'post'){
		$outData = array();
		$values = json_encode($data);
		$url = $this->getApiUrl($action);
		$uid =  $this->getUserId();
		$token = $this->getToken();
		$email = Mage::getStoreConfig('carebyzinc/api/xuser_email');
		if(($action =='price_quotes/generate') || ($action == 'policies' ))
			$header =  array( "Content-Type: application/json","uid:$uid","X-User-Token:$token","X-User-Email:$email","token-type:Bearer");
		elseif($action == 'token')
			$header =  array( "Content-Type: application/json","X-User-Token:$token","X-User-Email:$email","token-type:Bearer");

		else
			$header =  array( "Content-Type: application/json","uid:$uid","token-type:Bearer");
		
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_URL, $url);
		if($method == 'post'){
			curl_setopt($ch, CURLOPT_POST,TRUE);
			curl_setopt($ch, CURLOPT_POSTFIELDS,$values);
		}
		curl_setopt($ch, CURLOPT_HEADER ,FALSE); 
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER ,TRUE); 
		curl_setopt($ch,CURLOPT_SSL_VERIFYPEER, false);
		$outData['response'] = curl_exec($ch);
		$outData['code']     = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		return $outData;
	}
	
	 public function getwarrantyStatus( $item_id )
  	  {
  	  	if($item_id < 0){
	  		return '';
		}
  	  	$cart = Mage::getSingleton('checkout/cart');
		$quoteItem = $cart->getQuote()->getItemById($item_id);
	        $product = Mage::getModel('catalog/product')->load($quoteItem->getProductId()); 
	        if(($product->getCarebyzinc() != 1) && ($quoteItem->getCarebyzincOption())){	 
				 if ($additionalOption = $quoteItem->getOptionByCode('additional_options'))
				 {
					$additionalOptions = (array) unserialize($additionalOption->getValue());
				 }
				 $additionalOptionsArray = array();
				 foreach($additionalOptions as $option){	 
				 	if($option['label'] != 'carebyzinc')
				 		 $additionalOptionsArray[] =  $option;
				 }
				 $quoteItem->setCarebyzincOption('');
				
				 if(! empty($additionalOptionsArray)){
					 $quoteItem->addOption(array(	'product_id' => $quoteItem->getProductId(),
									'code' => 'additional_options',
									'value' => serialize($additionalOptionsArray)
								));
				 }else{
					 $quoteItem->getOptionByCode('additional_options')->delete();
				 }
				 $productPrice = $quoteItem->getProduct()->getFinalPrice();
				 $quoteItem->setCustomPrice($productPrice);
				 $quoteItem->setOriginalCustomPrice($productPrice);
				 $quoteItem->getProduct()->setIsSuperMode(true);			
				 $quoteItem->save();
				 $cart->getQuote()->collectTotals()->save();
			
		 }
		 return true;
    	}
    	 public function getCategoryArray()
	{
		
		
		$dataArray = $this->getCategoryJson();
		$category = array(''=>'Please Select');
		foreach($dataArray as $key => $value){
			$category[$key] = $key;		
		}
		return $category;

	}
	 public function getSubCategoryArray($category)
	{
		if($category)
			$dataArray = $this->getCategoryJson();		
		$subcategory = array();
		foreach($dataArray as $key => $value){
			if($key == $category){
				foreach($value as $val){
					$subcategory[$val] = $val;
				}
			}
		}		
		return $subcategory;

	}
	public function getCategoryJson()
	{		
	 	
	   	$outData = $this->callApi('','categories','get');
	   	if($outData['code'] == '200')
	   		$response = $outData['response'];
	   	else    
			$response = '{"Jewelry": ["Bracelet", "Necklace","Pendant","Brooch","Engagement Ring","Wedding Ring","Other Ring","Other"]}';
		$dataArray = json_decode($response);
		return $dataArray;
	
	}
	
    
}
