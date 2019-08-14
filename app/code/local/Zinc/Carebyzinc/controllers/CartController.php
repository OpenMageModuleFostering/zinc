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
     * Add product to shopping cart action
     *
     * @return Mage_Core_Controller_Varien_Action
     * @throws Exception
     */
    public function addAction()
    {
        if (!$this->_validateFormKey()) {
            $this->_goBack();
            return;
        }
        $cart   = $this->_getCart();
        $params = $this->getRequest()->getParams();
        
        try {
            if (isset($params['qty'])) {
                $filter = new Zend_Filter_LocalizedToNormalized(
                    array('locale' => Mage::app()->getLocale()->getLocaleCode())
                );
                $params['qty'] = $filter->filter($params['qty']);
            }

            $product = $this->_initProduct();
            $related = $this->getRequest()->getParam('related_product');
            
            /**
             * Check product availability
             */
            if (!$product) {
                $this->_goBack();
                return;
            }
            
            $carebyzinc = $product->getCarebyzinc();
            $isProduct = isset($params['isProduct']) && (int)$params['isProduct'] == 1 ? true : false;
            
            $interstitial = Mage::getStoreConfig('carebyzinc/display/interstitial');
            
            if($carebyzinc == '1' && $isProduct && $interstitial == '1') {
                if(!isset($params['carebyzinc_option'])) {
                    $cbzCategory = $product->getCarebyzincCategory();

                    if($carebyzinc == '1' && !empty($cbzCategory)) {
                        $model = Mage::getModel('carebyzinc/order');
                        $price = $model->getInterstitalPrice($product);
                        $result = $model->prepareInterstitials($cbzCategory);
                        $result['real_price'] = $price;
                        $result['qty'] = $params['qty'];
                        $sess = Mage::getSingleton("core/session",  array("name"=>"frontend"));
                        $sess->setData('interstitials', $result);

                        $url = $this->_getSession()->getRedirectUrl(true);
                        if ($url) {
                            $this->getResponse()->setRedirect($url);
                        } else {
                            $this->_redirectReferer(Mage::helper('checkout/cart')->getCartUrl());
                        }
                    }
                } else {
                    $cart->addProduct($product, $params);
                    if (!empty($related)) {
                        $cart->addProductsByIds(explode(',', $related));
                    }

                    $cart->save();
                    $this->_getSession()->setCartWasUpdated(true);

                    /**
                     * @todo remove wishlist observer processAddToCart
                     */
                    Mage::dispatchEvent('checkout_cart_add_product_complete',
                        array('product' => $product, 'request' => $this->getRequest(), 'response' => $this->getResponse())
                    );

                    if (!$this->_getSession()->getNoCartRedirect(true)) {
                        if (!$cart->getQuote()->getHasError()) {
                            $message = $this->__('%s was added to your shopping cart.', Mage::helper('core')->escapeHtml($product->getName()));
                            $this->_getSession()->addSuccess($message);
                        }
                        $this->_goBack();
                    }
                }
            } else {
                $cart->addProduct($product, $params);
                if (!empty($related)) {
                    $cart->addProductsByIds(explode(',', $related));
                }

                $cart->save();
                $this->_getSession()->setCartWasUpdated(true);

                /**
                 * @todo remove wishlist observer processAddToCart
                 */
                Mage::dispatchEvent('checkout_cart_add_product_complete',
                    array('product' => $product, 'request' => $this->getRequest(), 'response' => $this->getResponse())
                );
                
                if (!$this->_getSession()->getNoCartRedirect(true)) {
                    if (!$cart->getQuote()->getHasError()) {
                        $message = $this->__('%s was added to your shopping cart.', Mage::helper('core')->escapeHtml($product->getName()));
                        $this->_getSession()->addSuccess($message);
                    }
                    $this->_goBack();
                }
            }
        } catch (Mage_Core_Exception $e) {
            if ($this->_getSession()->getUseNotice(true)) {
                $this->_getSession()->addNotice(Mage::helper('core')->escapeHtml($e->getMessage()));
            } else {
                $messages = array_unique(explode("\n", $e->getMessage()));
                foreach ($messages as $message) {
                    $this->_getSession()->addError(Mage::helper('core')->escapeHtml($message));
                }
            }

            $url = $this->_getSession()->getRedirectUrl(true);
            if ($url) {
                $this->getResponse()->setRedirect($url);
            } else {
                $this->_redirectReferer(Mage::helper('checkout/cart')->getCartUrl());
            }
        } catch (Exception $e) {
            $this->_getSession()->addException($e, $this->__('Cannot add the item to shopping cart.'));
            Mage::logException($e);
            $this->_goBack();
        }
    }
    
    /**
     * Process product adding to cart
     */
    public function ajaxProcessItemAction()
    {
        $response = false;
        $cart   = $this->_getCart();
        $params = $this->getRequest()->getParams();
        $post = $this->getRequest()->getPost();
        
        if(is_array($post)) {
            $pId = (int)$post['pId'];
            $product = Mage::getModel('catalog/product')->load($pId);
            
            /**
             * Check product availability
             */
            if (!$product) {
                $this->_goBack();
                return;
            }
            
            $model = Mage::getModel('carebyzinc/order');
            $response = $model->processPurchaseInsurance($post);

            if($response) {
                switch($response['action']) {
                    case 'cancel':
                        $params['qty'] = (int)$post['qty'];
                        $cart->addProduct($product, $params);
                        if (!empty($related)) {
                            $cart->addProductsByIds(explode(',', $related));
                        }

                        $cart->save();
                        $this->_getSession()->setCartWasUpdated(true);

                        /**
                         * @todo remove wishlist observer processAddToCart
                         */
                        Mage::dispatchEvent('checkout_cart_add_product_complete',
                            array('product' => $product, 'request' => $this->getRequest(), 'response' => $this->getResponse())
                        );

                        $message = $this->__('%s was added to your shopping cart.', Mage::helper('core')->escapeHtml($product->getName()));
                        $this->_getSession()->addSuccess($message);
                        break;
                    case 'add':
                        break;
                }
            }

            $sess = Mage::getSingleton("core/session",  array("name"=>"frontend"));
            $sess->unsetData('interstitials');
        }
        
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($response['url']));
    }
    
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
			$flag = 0;
			if(($productType == Mage_Catalog_Model_Product_Type::TYPE_SIMPLE || $productType == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE) && ($product->getCarebyzinc() == 1)) {
				$qty = 1;
				$flag = 1;
			}           
            
            if($product->getId() == Mage::getStoreConfig('carebyzinc/general/warranty_product')){
				$flag = 0;	
				if($qty >1){
					$params['qty'] = 1;
					$qty = 1;
				}
			}
            
            if (isset($params['qty'])) {
				if($flag) {
					$qty = $params['qty'];
					if($qty > 1)
						$params['qty'] = 1;
				}
                $filter = new Zend_Filter_LocalizedToNormalized(
                    array('locale' => Mage::app()->getLocale()->getLocaleCode())
                );
                $params['qty'] = $filter->filter($params['qty']);
            } 
			
            $item = $cart->updateItem($id, new Varien_Object($params));
            
            if($flag) { 
				if($qty >1) {
					$quote = Mage::getSingleton('checkout/session')->getQuote();
					for($i=0; $i<($qty-1); $i++) {						
						$result = $quote->addProduct($product, $item->getBuyRequest());
						$result = ($result->getParentItem() ? $result->getParentItem() : $result);
                        $quote->save();
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
					if($oldQuoteItem) {
                        $product = Mage::getModel('catalog/product')
                            ->setStoreId(Mage::app()->getStore()->getId())
                            ->load($oldQuoteItem->getProductId());
                        $productType = $product->getTypeId();

                        if(($productType == Mage_Catalog_Model_Product_Type::TYPE_SIMPLE || $productType == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE) && ($product->getCarebyzinc() == 1)) {						
                            $qty = 1;
                            $flag = 1;						
                        }

                        if (isset($data['qty'])) {
                            if($flag){
                                $qty = $data['qty'];
                                if($qty >1)
                                    $data['qty'] = 1;
                            }
                            if($product->getId() == Mage::getStoreConfig('carebyzinc/general/warranty_product')){
                                $flag = 0;	
                                if($data['qty'] >1){
                                    $data['qty'] = 1;
                                    $qty = 1;
                                }
                            }
                            $cartData[$index]['qty'] = $filter->filter(trim($data['qty']));
                        }

                        if($flag) {
                            if($qty > 1) {
                                for($i=0; $i<($qty-1); $i++) {
                                    $result = $quote->addProduct($product, $oldQuoteItem->getBuyRequest());	
                                    $result = ($result->getParentItem() ? $result->getParentItem() : $result);
                                    $quote->save();
                                }
                            }
                        }
                    }
                }

                if (!$cart->getCustomerSession()->getCustomer()->getId() && $cart->getQuote()->getCustomerId()) {
                    $cart->getQuote()->setCustomerId(null);
                }
                
                $cartData = $cart->suggestItemsQty($cartData);
                $cart->updateItems($cartData)->save();
            }
            $this->_getSession()->setCartWasUpdated(true);
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError(Mage::helper('core')->escapeHtml($e->getMessage()));
        } catch (Exception $e) {
            $this->_getSession()->addException($e, $this->__('Cannot update shopping cart.'));
            Mage::logException($e);
        }
    }
    
    
     /**
     * Minicart delete action
     */
    public function ajaxDeleteAction()
    {
        if (!$this->_validateFormKey()) {
            Mage::throwException('Invalid form key');
        }
        $id = (int) $this->getRequest()->getParam('id');
        $result = array();
        if ($id) {
            try {
				
				if(Mage::getStoreConfig('carebyzinc/general/enabled')){
					$cartHelper = Mage::helper('checkout/cart');
					$cart = Mage::getSingleton('checkout/cart');
					$_item = $cart->getQuote()->getItemById($id);	
					$itemId =  $_item->getId();
					$productId =  $_item->getProductId();
					if($_item->getCarebyzincVariantid()){				
						$cart = Mage::getModel('checkout/cart')->getQuote();
						foreach ($cart->getAllItems() as $item) {
							if($item->getCarebyzincParentid() == $itemId)
								$cartHelper->getCart()->removeItem($item->getId())->save();	   					
							
						}
					}else{
						if($productId == Mage::getStoreConfig('carebyzinc/general/warranty_product')){				
							$itemId =  $_item->getCarebyzincParentid();
							$cart = Mage::getSingleton('checkout/cart');
							$item = $cart->getQuote()->getItemById($itemId);			
							$item->setCarebyzincVariantid(NULL);
							$item->save();
						}
					}
					
				}				
				
                $this->_getCart()->removeItem($id)->save();

                $result['qty'] = $this->_getCart()->getSummaryQty();

                $this->loadLayout();
                $result['content'] = $this->getLayout()->getBlock('minicart_content')->toHtml();

                $result['success'] = 1;
                $result['message'] = $this->__('Item was removed successfully.');
                Mage::dispatchEvent('ajax_cart_remove_item_success', array('id' => $id));
            } catch (Exception $e) {
                $result['success'] = 0;
                $result['error'] = $this->__('Can not remove the item.');
            }
        }

        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }
    
    
    /**
     * Minicart ajax update qty action
     */
    public function ajaxUpdateAction()
    {
        if (!$this->_validateFormKey()) {
            Mage::throwException('Invalid form key');
        }
        $id = (int)$this->getRequest()->getParam('id');
        $qty = $this->getRequest()->getParam('qty');
        $result = array();
        if ($id) {
            try {
                $cart = $this->_getCart();
                $quoteItem = $cart->getQuote()->getItemById($id);
                if (!$quoteItem) {
                    Mage::throwException($this->__('Quote item is not found.'));
                }
                $product = Mage::getModel('catalog/product')
								->setStoreId(Mage::app()->getStore()->getId())
								->load($quoteItem->getProductId());
              	if($product->getCarebyzinc() == 1)
				{						
					$qty = 1;
					$flag = 1;						
				}	
				if($product->getId() == Mage::getStoreConfig('carebyzinc/general/warranty_product')){
						$flag = 0;	
					if($qty >1){
							$qty = 1;
					}
				}
                if (isset($qty)) {
                    $filter = new Zend_Filter_LocalizedToNormalized(
                        array('locale' => Mage::app()->getLocale()->getLocaleCode())
                    );
                    $qty = $filter->filter($qty);
                }

                
                if ($qty == 0) {
                    $cart->removeItem($id);
                } else {
                    $quoteItem->setQty($qty)->save();
                }
                $this->_getCart()->save();

                $this->loadLayout();
                $result['content'] = $this->getLayout()->getBlock('minicart_content')->toHtml();

                $result['qty'] = $this->_getCart()->getSummaryQty();

                if (!$quoteItem->getHasError()) {
                    $result['message'] = $this->__('Item was updated successfully.');
                } else {
                    $result['notice'] = $quoteItem->getMessage();
                }
                $result['success'] = 1;
            } catch (Exception $e) {
                $result['success'] = 0;
                $result['error'] = $this->__('Can not save item.');
            }
        }

        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }
}
