<?php
 /**
 * ZincPlatform
 * @package    Zinc_Carebyzinc
 * @copyright  Copyright (c) 2016-2017 Zinplatform (http://www.zincplatform.com/)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */ 
class Zinc_Carebyzinc_Model_Checkout_Multishipping extends Mage_Checkout_Model_Type_Multishipping
{
   
    /**
     * Assign quote items to addresses and specify items qty
     *
     * array structure:
     * array(
     *      $quoteItemId => array(
     *          'qty'       => $qty,
     *          'address'   => $customerAddressId
     *      )
     * )
     *
     * @param array $info
     * @return Mage_Checkout_Model_Type_Multishipping
     */
    public function setShippingItemsInformation($info)
    {
        if (is_array($info)) {
            $allQty = 0;
            $itemsInfo = array();
            foreach ($info as $itemData) {
                foreach ($itemData as $quoteItemId => $data) {
                    $allQty += $data['qty'];
                    $itemsInfo[$quoteItemId] = $data;
                }
            }

            $maxQty = (int)Mage::getStoreConfig('shipping/option/checkout_multiple_maximum_qty');
            if ($allQty > $maxQty) {
                Mage::throwException(Mage::helper('checkout')->__('Maximum qty allowed for Shipping to multiple addresses is %s', $maxQty));
            }
            $quote = $this->getQuote();
            $addresses  = $quote->getAllShippingAddresses();
            foreach ($addresses as $address) {
                $quote->removeAddress($address->getId());
            }

            foreach ($info as $itemData) {
                foreach ($itemData as $quoteItemId => $data) {
                    $this->_addShippingItem($quoteItemId, $data);
                }
            }

            /**
             * Delete all not virtual quote items which are not added to shipping address
             * MultishippingQty should be defined for each quote item when it processed with _addShippingItem
             */
            foreach ($quote->getAllItems() as $_item) {
                if (!$_item->getProduct()->getIsVirtual() &&
                    !$_item->getParentItem() &&
                    !$_item->getMultishippingQty()
                ) {
                    $quote->removeItem($_item->getId());
                }
            }

            if ($billingAddress = $quote->getBillingAddress()) {
                $quote->removeAddress($billingAddress->getId());
            }

            if ($customerDefaultBilling = $this->getCustomerDefaultBillingAddress()) {
                $quote->getBillingAddress()->importCustomerAddress($customerDefaultBilling);
            }

            foreach ($quote->getAllItems() as $_item) {
                if (!$_item->getProduct()->getIsVirtual()) {
                    continue;
                }

                if (isset($itemsInfo[$_item->getId()]['qty'])) {
                    if ($qty = (int)$itemsInfo[$_item->getId()]['qty']) {
						if(Mage::getStoreConfig('carebyzinc/general/enabled')){
							$qty = 1;
							$itemsInfo[$_item->getId()]['qty'] = 1;
						}
                        $_item->setQty($qty);
                        $quote->getBillingAddress()->addItem($_item);
                    } else {
                        $_item->setQty(0);
                        $quote->removeItem($_item->getId());
                    }
                 }

            }

            $this->save();
            Mage::dispatchEvent('checkout_type_multishipping_set_shipping_items', array('quote'=>$quote));
        }
        return $this;
    }
    
    protected function _addShippingItem($quoteItemId, $data)
    {
		
        $qty       = isset($data['qty']) ? (int) $data['qty'] : 1;
        //$qty       = $qty > 0 ? $qty : 1;
        $addressId = isset($data['address']) ? $data['address'] : false;
        $quoteItem = $this->getQuote()->getItemById($quoteItemId);
		if(Mage::getStoreConfig('carebyzinc/general/enabled')){
			if($qty>1){
				$limit = $qty;
				$qty = 1;
				if($quoteItem->getProductId() != Mage::getStoreConfig('carebyzinc/general/warranty_product')){
					for($i =1; $i<$limit;$i++){
						$product = Mage::getModel('catalog/product')->load($quoteItem->getProductId());		
						$quote = Mage::getSingleton('checkout/session')->getQuote();
						$result = $quote->addProduct($product, $quoteItem->getBuyRequest());
						$quote->collectTotals()->save();	
						
						if ($addressId && $result) {
							/**
							 * Skip item processing if qty 0
							 */
							if ($qty === 0) {
								return $this;
							}
							$quoteItem->setMultishippingQty((int)$result->getMultishippingQty());
							$quoteItem->setQty($result->getMultishippingQty());
							$address = $this->getCustomer()->getAddressById($addressId);
							if ($address->getId()) {
								if (!$quoteAddress = $this->getQuote()->getShippingAddressByCustomerAddressId($address->getId())) {
									$quoteAddress = Mage::getModel('sales/quote_address')->importCustomerAddress($address);
									$this->getQuote()->addShippingAddress($quoteAddress);
									if ($couponCode = $this->getCheckoutSession()->getCartCouponCode()) {
										$this->getQuote()->setCouponCode($couponCode);
									}
								}

								$quoteAddress = $this->getQuote()->getShippingAddressByCustomerAddressId($address->getId());
								if ($quoteAddressItem = $quoteAddress->getItemByQuoteItemId($result->getId())) {
									$quoteAddressItem->setQty((int)($quoteAddressItem->getQty()));
								} else {
									$quoteAddress->addItem($result, $qty);
								}
								/**
								 * Require shiping rate recollect
								 */
								$quoteAddress->setCollectShippingRates((boolean) $this->getCollectRatesFlag());
							}
						}
						
					}
					
				}
			
			}		
		}		
        if ($addressId && $quoteItem) {
            /**
             * Skip item processing if qty 0
             */
            if ($qty === 0) {
                return $this;
            }
            $quoteItem->setMultishippingQty((int)$quoteItem->getMultishippingQty()+$qty);
            $quoteItem->setQty($quoteItem->getMultishippingQty());
            $address = $this->getCustomer()->getAddressById($addressId);
            if ($address->getId()) {
                if (!$quoteAddress = $this->getQuote()->getShippingAddressByCustomerAddressId($address->getId())) {
                    $quoteAddress = Mage::getModel('sales/quote_address')->importCustomerAddress($address);
                    $this->getQuote()->addShippingAddress($quoteAddress);
                    if ($couponCode = $this->getCheckoutSession()->getCartCouponCode()) {
                        $this->getQuote()->setCouponCode($couponCode);
                    }
                }

                $quoteAddress = $this->getQuote()->getShippingAddressByCustomerAddressId($address->getId());
                if ($quoteAddressItem = $quoteAddress->getItemByQuoteItemId($quoteItemId)) {
                    $quoteAddressItem->setQty((int)($quoteAddressItem->getQty()+$qty));
                } else {
                    $quoteAddress->addItem($quoteItem, $qty);
                }
                /**
                 * Require shiping rate recollect
                 */
                $quoteAddress->setCollectShippingRates((boolean) $this->getCollectRatesFlag());
            }
        }
        return $this;
    }
}
