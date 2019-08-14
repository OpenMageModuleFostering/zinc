<?php
 /**
 * ZincPlatform
 * @package    Zinc_Carebyzinc
 * @copyright  Copyright (c) 2016-2017 Zinplatform (http://www.zincplatform.com/)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */ 
 
/**
 * Shopping cart controller
 */
require_once 'Mage/Checkout/controllers/CartController.php';
class Zinc_Carebyzinc_CartController extends Mage_Checkout_CartController
{   
    /**
     * Update product configuration for a cart item
     */
    public function updateItemOptionsAction()
    {
        $cart   = $this->_getCart();
        $id = (int) $this->getRequest()->getParam('id');
        $params = $this->getRequest()->getParams();		
        if (!isset($params['options'])) {
            $params['options'] = array();
        }
        
        try {
			$quoteItem = $cart->getQuote()->getItemById($id);
            if (!$quoteItem) {
                Mage::throwException($this->__('Quote item is not found.'));
            }
           
			$product = Mage::getModel('catalog/product')->load($quoteItem->getProductId()); 
			$productType = $product->getTypeId();
			$carebyzinc = '';
			$flag = 0;$price =  0;
			$additionalOptions = array();
			if(($productType == Mage_Catalog_Model_Product_Type::TYPE_SIMPLE || $productType == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE) && ($product->getCarebyzinc() == 1))
			{
				$newPrice =	$quoteItem->getPrice();
				$qty = 1;
				$flag = 1;
			}           
            
            if (isset($params['qty'])) {
				if($flag){
					$qty = $params['qty'];
					$params['qty'] = 1;
				}
                $filter = new Zend_Filter_LocalizedToNormalized(
                    array('locale' => Mage::app()->getLocale()->getLocaleCode())
                );
                $params['qty'] = $filter->filter($params['qty']);
            }           
           
			if($flag)
			{	
				$carebyzincOption = $params['carebyzinc_option'];
				if ($additionalOption = $quoteItem->getOptionByCode('additional_options'))
					{
						$additionalOptions = (array) unserialize($additionalOption->getValue());
					}	
				if($carebyzincOption){
					$priceQuote = Mage::getSingleton('core/session')->getCareQuote();
					$carebyzincAry = $priceQuote[$carebyzincOption];
					$price = $carebyzincAry['price_per_year']?$carebyzincAry['price_per_year']:0;					
					$carebyzinc = serialize($priceQuote[$carebyzincOption]);
					foreach($additionalOptions as $option){
						if($option['label'] == 'carebyzinc'){
							$option['value'] = $carebyzincOption;
							break;
						}	
					}
					
					Mage::getSingleton('core/session')->unsCareQuote();	
								
				}else{					
					$carebyzinc = $quoteItem->getCarebyzincOption();
					$price = $quoteItem->getCarebyzincPrice();	
				}				
 
			}
            $item = $cart->updateItem($id, new Varien_Object($params));
            
            if($flag){ 
				$productPrice = $item->getProduct()->getFinalPrice();
				$newPrice = $productPrice + $price;		
				if($carebyzinc){         
					$item->setCarebyzincOption($carebyzinc);
					$item->addOption(array(
											'product_id' => $item->getProductId(),
											'code' => 'additional_options',
											'value' => serialize($additionalOptions)
										));
					$item->setCarebyzincPrice($price);		
					$item->setCustomPrice($newPrice);
					$item->setOriginalCustomPrice($newPrice);
					$item->getProduct()->setIsSuperMode(true);  
				}   
				if($qty >1){
					$quote = Mage::getSingleton('checkout/session')->getQuote();
					for($i = 1;$i<=($qty-1);$i++){						
						$result = $quote->addProduct($product, $item->getBuyRequest());	
						$result = ( $result->getParentItem() ? $result->getParentItem() : $result );
						if($carebyzinc){ 
							$result->setCarebyzincOption($carebyzinc);
							$result->setCarebyzincPrice($price);	
							$result->addOption(array(
									'product_id' => $item->getProductId(),
									'code' => 'additional_options',
									'value' => serialize($additionalOptions)
								));	
							
							$result->setCustomPrice($newPrice);
							$result->setOriginalCustomPrice($newPrice);		
						}             
					}
				}
					     
			}
			
            if (is_string($item)) {
                Mage::throwException($item);
            }
            if ($item->getHasError()) {
                Mage::throwException($item->getMessage());
            }

            $related = $this->getRequest()->getParam('related_product');
            if (!empty($related)) {
                $cart->addProductsByIds(explode(',', $related));
            }

            $cart->save();

            $this->_getSession()->setCartWasUpdated(true);

            Mage::dispatchEvent('checkout_cart_update_item_complete',
                array('item' => $item, 'request' => $this->getRequest(), 'response' => $this->getResponse())
            );
            if (!$this->_getSession()->getNoCartRedirect(true)) {
                if (!$cart->getQuote()->getHasError()) {
                    $message = $this->__('%s was updated in your shopping cart.', Mage::helper('core')->escapeHtml($item->getProduct()->getName()));
                    $this->_getSession()->addSuccess($message);
                }
                $this->_goBack();
            }
        } catch (Mage_Core_Exception $e) {
            if ($this->_getSession()->getUseNotice(true)) {
                $this->_getSession()->addNotice($e->getMessage());
            } else {
                $messages = array_unique(explode("\n", $e->getMessage()));
                foreach ($messages as $message) {
                    $this->_getSession()->addError($message);
                }
            }

            $url = $this->_getSession()->getRedirectUrl(true);
            if ($url) {
                $this->getResponse()->setRedirect($url);
            } else {
                $this->_redirectReferer(Mage::helper('checkout/cart')->getCartUrl());
            }
        } catch (Exception $e) {
            $this->_getSession()->addException($e, $this->__('Cannot update the item.'));
            Mage::logException($e);
            $this->_goBack();
        }
        $this->_redirect('*/*');
    }
    
     /**
     * Update customer's shopping cart
     */
    protected function _updateShoppingCart()
    {
        try {
            $cartData = $this->getRequest()->getParam('cart');
            if (is_array($cartData)) {
                $filter = new Zend_Filter_LocalizedToNormalized(
                    array('locale' => Mage::app()->getLocale()->getLocaleCode())
                );
                 $cart = $this->_getCart();
                foreach ($cartData as $index => $data) {
					$quote = Mage::getSingleton('checkout/session')->getQuote();
					$oldQuoteItem = $quote->getItemById($index);
					$flag = 0;
					if($oldQuoteItem){
					$product = Mage::getModel('catalog/product')
								->setStoreId(Mage::app()->getStore()->getId())
								->load($oldQuoteItem->getProductId());
					$productType = $product->getTypeId();
					$newPrice = 0;$price = 0;$additionalOptions = array();
					if(($productType == Mage_Catalog_Model_Product_Type::TYPE_SIMPLE || $productType == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE) && ($product->getCarebyzinc() == 1))
					{
						$additionalOption = $oldQuoteItem->getOptionByCode('additional_options');
						$newPrice =	$oldQuoteItem->getPrice();
						$price = $oldQuoteItem->getCarebyzincPrice();
						$qty = 1;
						$flag = 1;
						if ($additionalOption = $oldQuoteItem->getOptionByCode('additional_options'))
						{
							$additionalOptions = (array) unserialize($additionalOption->getValue());
						}
					}	
                    if (isset($data['qty'])) {
						if($flag){
							$qty = $data['qty'];
							$data['qty'] = 1;
						}
                        $cartData[$index]['qty'] = $filter->filter(trim($data['qty']));
                    }
                    if($flag){
						if($qty >1){
							for($i = 1;$i<=($qty-1);$i++){
								
								$result = $quote->addProduct($product, $oldQuoteItem->getBuyRequest());	
								$result = ( $result->getParentItem() ? $result->getParentItem() : $result );
								$result->setCarebyzincOption($oldQuoteItem->getCarebyzincOption());
								$result->setCarebyzincPrice($price);	
								$result->addOption(array(
										'product_id' => $result->getProductId(),
										'code' => 'additional_options',
										'value' => serialize($additionalOptions)
									));	
								
								$result->setCustomPrice($newPrice);
								$result->setOriginalCustomPrice($newPrice);		             
							}
						}
					}
                }}

                if (! $cart->getCustomerSession()->getCustomer()->getId() && $cart->getQuote()->getCustomerId()) {
                    $cart->getQuote()->setCustomerId(null);
                }

                $cartData = $cart->suggestItemsQty($cartData);
                $cart->updateItems($cartData)
                    ->save();
            }
            $this->_getSession()->setCartWasUpdated(true);
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError(Mage::helper('core')->escapeHtml($e->getMessage()));
        } catch (Exception $e) {
            $this->_getSession()->addException($e, $this->__('Cannot update shopping cart.'));
            Mage::logException($e);
        }
    }

}
