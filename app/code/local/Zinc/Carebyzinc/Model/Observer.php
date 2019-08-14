<?php
 /**
 * ZincPlatform
 * @package    Zinc_Carebyzinc
 * @copyright  Copyright (c) 2016-2017 Zinplatform (http://www.zincplatform.com/)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */ 
class Zinc_Carebyzinc_Model_Observer 
{
	public function saveCarebyzinc($observer)
	{
		if(!Mage::registry('carebyzinc_save')) {
            $order = $observer->getEvent()->getOrder();
			$model = Mage::getModel('carebyzinc/order');
			$model->savePolicy($order);
			Mage::register('carebyzinc_save',true);
            
            $model->recordOrderToZinc($order);
        }
	}
	
	public function setCarebyzinc($observer)
	{
		if(Mage::getStoreConfig('carebyzinc/general/enabled')) {
            $order = $observer->getEvent()->getOrder();
            $orderId = $order->getId();
            $orderItems = $order->getAllItems();
            foreach($orderItems as $item) {
                if(($item->getCarebyzincParentid()) && ($item->getCarebyzincOption())) {
                    $careParentId = 0;
                    $orderItemCollection = Mage::getModel('sales/order_item')->getCollection()
                        ->addFieldToFilter('quote_item_id', $item->getCarebyzincParentid())
                        ->addFieldToFilter('order_id', $orderId);
                    
                    foreach($orderItemCollection as $col) {
                        $careParentId = $col->getItemId();				
                    }
                    
                    if($careParentId){
                        $item->setCarebyzincParentid((int)$careParentId);
                        $item->save();
                    }
                }
            }
        }
	}
	
	public function cartLoad($observer)
	{
		if(!Mage::getStoreConfig('carebyzinc/general/enabled')){
			$cartHelper = Mage::helper('checkout/cart');
			$cart = Mage::getModel('checkout/cart')->getQuote();
			$idArray = array();
			foreach ($cart->getAllItems() as $item) {
			    if($item->getCarebyzincVariantid()) {
					$item->setCarebyzincVariantid(NULL);	
					$item->save();
					$idArray[] = $item->getId();		       
			    } else {
					if(($item->getProductId() == Mage::getStoreConfig('carebyzinc/general/warranty_product')) || (in_array($item->getCarebyzincParentid(),$idArray)) ){
						$cartHelper->getCart()->removeItem($item->getId())->save();
					}
				}			   
			}
		} else {
			$cart = Mage::getModel('checkout/cart')->getQuote();
			foreach ($cart->getAllItems() as $item) {				
				if($item->getProductId() == Mage::getStoreConfig('carebyzinc/general/warranty_product')) {
					$model = Mage::getModel('carebyzinc/carebyzinc');	
					$carebyStatus = $model->getwarrantyStatus($item->getCarebyzincParentid(),$item->getId());
				}
			}
		}
	}
	
	public function warrantyDelete($observer)
	{
		$_item = $observer->getEvent()->getQuoteItem();
		$itemId =  $_item->getId();
		$productId =  $_item->getProductId();
		if(Mage::getStoreConfig('carebyzinc/general/enabled')) {
			$cartHelper = Mage::helper('checkout/cart');
			if($_item->getCarebyzincVariantid()) {
				$cart = Mage::getModel('checkout/cart')->getQuote();
				foreach ($cart->getAllItems() as $item) {
					if($item->getCarebyzincParentid() == $itemId) {
                        $cartHelper->getCart()->removeItem($item->getId())->save();
                    }
				}
			} else {
				if($productId == Mage::getStoreConfig('carebyzinc/general/warranty_product')) {
					$itemId =  $_item->getCarebyzincParentid();
					$cart = Mage::getSingleton('checkout/cart');
					$item = $cart->getQuote()->getItemById($itemId);
					if($item) {
						$item->setCarebyzincVariantid(NULL);
						$item->save();
					}
				}
			}
		}
	}

    public function salesQuoteItemSetProduct(Varien_Event_Observer $observer)
    {
        /* @var $item Mage_Sales_Model_Quote_Item */
        $item = $observer->getQuoteItem();
        $name = '';
        
		if($item->getProductId() == Mage::getStoreConfig('carebyzinc/general/warranty_product')) {
			$quoteItem = Mage::getModel('sales/quote_item')->load($item->getCarebyzincParentid());
			$carebyzincAry = (array) unserialize($item->getCarebyzincOption()); 
			
			if($quoteItem) {
                $name = $item->getName().' for '.$quoteItem->getName();
            }
			if($name) {
                $item->setName($name);
            }
			if($carebyzincAry['quote_type']) {
                $item->setSku($carebyzincAry['quote_type']);
            }
		}
        
        return $this;
    }
}
