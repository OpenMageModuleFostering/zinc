<?php
 /**
 * ZincPlatform
 * @package    Zinc_Carebyzinc
 * @copyright  Copyright (c) 2016-2017 Zinplatform (http://www.zincplatform.com/)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */ 
class Zinc_Carebyzinc_IndexController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
		$this->loadLayout();     
		$this->renderLayout();
    }
	
	public function priceQuoteAction()
    {
	  $product_id = $this->getRequest()->getParam('pid');
	  $configOptions = $this->getRequest()->getParam('configOptions');
	  $customoptionPrice = $this->getRequest()->getParam('customoptionPrice');
	  
	  $configOptionsArray =  (array)json_decode($configOptions);
	  $zip = $this->getRequest()->getParam('zip');
	  if($product_id < 0){
	   return '';
	  }
	  $response = '';
	  $this->loadLayout();  
	  $product = Mage::getModel('catalog/product')->load($product_id); 
	  if(! empty($configOptionsArray)){
	  	//$childProduct = Mage::getModel('catalog/product_type_configurable')->getProductByAttributes( $configOptionsArray, $product);
	 	//$product = Mage::getModel('catalog/product')->load($childProduct->getId()); 
	 	$attributes = $product->getTypeInstance(true)->getConfigurableAttributes($product);
		$priceVal = 0;
		foreach ($attributes as $attribute){
		    $prices = $attribute->getPrices();
		    foreach ($prices as $price){
		    	foreach($configOptionsArray as $key =>$value){
		   		if($price['value_index'] == $value ){
		       			if ($price['is_percent']){ 
		            		    $priceVal += (float)$price['pricing_value'] * $basePrice / 100;
		       			}
				        else { 
				            $priceVal += (float)$price['pricing_value'];
				        }
		       		 }
		       	 }
		    }
		}
		$customoptionPrice += $priceVal;
	 	
	 	
	 }
	 
	  $model = Mage::getModel('carebyzinc/carebyzinc');
	  $quoteBlock = $this->getLayout()->createBlock('carebyzinc/carebyzinc'); 
	  $itemId = $this->getRequest()->getParam('itemId');
	  if($itemId){
	 	$response = $model->getPriceQuoteinCart($product,$itemId,$zip);
	 	$response = is_array($response[$itemId])?$response[$itemId]:$response;
	  	$quoteBlock->setTemplate('carebyzinc/options/cart.phtml');
	  	$quoteBlock->setItemId($itemId); 
	  	
	  } else {
	   	 $response = $model->getPriceQuote($product,$zip,$customoptionPrice);
	 	 $quoteBlock->setTemplate('carebyzinc/options/default.phtml');	 	
	  }
	  $quoteBlock->setQuoteData($response);  
	 
	  $html = $quoteBlock->toHtml();
	  $this->getResponse()->setBody( Mage::helper('core')->jsonEncode($html));
    }
   
    public function updatePriceQuoteinCartAction()
    {
		$carebyzincId = $this->getRequest()->getParam('carebyzinc');
		$itemId = $this->getRequest()->getParam('itemId');
		$priceQuoteSession = Mage::getSingleton('core/session')->getCareByZincQuote();
		$priceQuote = $priceQuoteSession[$itemId];

		if($carebyzincItem = $priceQuote[$carebyzincId]){
			$additionalOptions[] = array(
							'label' => 'carebyzinc',
							'value' => $carebyzincId,
						);		
				
			$cart = Mage::getSingleton('checkout/cart');
			$item = $cart->getQuote()->getItemById($itemId);
			$item->getProduct()->setIsSuperMode(true);
			if($price = $carebyzincItem['price_per_year']){
				$product = Mage::getModel('catalog/product')->load($item->getProductId());
				//$productPrice = $product->getFinalPrice();
				$productPrice = $item->getProduct()->getFinalPrice();
				$newPrice = $productPrice + $price;	
				$item->setCarebyzincPrice($price);					
				$item->setCustomPrice($newPrice);
				$item->setOriginalCustomPrice($newPrice);
			}
			if ($additionalOption = $item->getOptionByCode('additional_options'))
			{
					$additionalOptions = (array) unserialize($additionalOption->getValue());
			}
			$item->setCarebyzincOption(serialize($priceQuote[$carebyzincId]));
			$item->addOption(array(
					'product_id' => $item->getProductId(),
					'code' => 'additional_options',
					'value' => serialize($additionalOptions)
				));
			$item->save();
			$cart->save();
			Mage::getSingleton('core/session')->unsCareByZincQuote();	
		}
		echo 'success';
    }
    public function removeWarrantyAction()
    {
	  $item_id = $this->getRequest()->getParam('id');
	  if($item_id < 0){
	  	return '';
	  }
	 $cart = Mage::getSingleton('checkout/cart');
	 $quoteItem = $cart->getQuote()->getItemById($item_id);
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
	 $productPrice = $quoteItem->getProduct()->getFinalPrice();
	 if(! empty($additionalOptionsArray)){
		 $quoteItem->addOption(array(	'product_id' => $quoteItem->getProductId(),
						'code' => 'additional_options',
						'value' => serialize($additionalOptionsArray)
					));
	 }else{
	 	$quoteItem->getOptionByCode('additional_options')->delete();
	 
	 }
	 $quoteItem->setCarebyzincPrice(0);		
	 $quoteItem->setCustomPrice($productPrice);
	 $quoteItem->setOriginalCustomPrice($productPrice);
	 $quoteItem->getProduct()->setIsSuperMode(true);			
	 $quoteItem->save();
	 $cart->getQuote()->collectTotals()->save();
	 $this->_redirect('checkout/cart/');
    }
   
}
