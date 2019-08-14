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
	  $basePrice = $product->getPrice();
	  if(! empty($configOptionsArray)){
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
	  	$quoteBlock->setZipData($zip);  
	  	$quoteBlock->setPId($product->getId());
	  	
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
		$additionalOptions = array();
		if($carebyzincItem = $priceQuote[$carebyzincId]){
						
			$cart = Mage::getSingleton('checkout/cart');
			$item = $cart->getQuote()->getItemById($itemId);			
			$item->setCarebyzincVariantid($carebyzincId);
			$item->save();
			$warrantPrdctId = Mage::getStoreConfig('carebyzinc/general/warranty_product');
			if($warrantPrdctId){				
				
				$req =  array('qty'=>1);
				$careParentId = $itemId;				
				$warrantyProduct = Mage::getModel('catalog/product')->load($warrantPrdctId);
				$productPrice = $warrantyProduct->getPrice();			
				
				if($price = $carebyzincItem['price_per_year'])
					$newPrice = $productPrice + $price;				
				$quote = Mage::getSingleton('checkout/session')->getQuote();

				$quoteItem = $quote->addProduct($warrantyProduct,1);
				$quoteItem->setCustomPrice($newPrice);
				$quoteItem->setOriginalCustomPrice($newPrice);
				$quoteItem->setCarebyzincPrice($price);
				$quoteItem->setCarebyzincParentid((int)$careParentId);
				$quoteItem->getProduct()->setIsSuperMode(true);
				
				if( $item = $priceQuote[$carebyzincId]){												
					$additionalOptions[] = array(
									'label' =>  'carebyzinc',
									'value' => $carebyzincId,
								);
					$quoteItem->setCarebyzincOption(serialize($priceQuote[$carebyzincId]));
		
				}
				$quoteItem->addOption(array(
					'product_id' => $quoteItem->getProductId(),
					'code' => 'additional_options',
					'value' => serialize($additionalOptions)
				));
				
				$quote->collectTotals()->save();

			}
			
			Mage::getSingleton('core/session')->unsCareByZincQuote();	
		}
		echo 'success';
    }
    
  
}
