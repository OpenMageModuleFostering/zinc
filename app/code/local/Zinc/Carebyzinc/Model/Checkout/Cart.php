<?php

/**
 * ZincPlatform
 * @package    Zinc_Carebyzinc
 * @copyright  Copyright (c) 2016-2017 Zinplatform (http://www.zincplatform.com/)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Zinc_Carebyzinc_Model_Checkout_Cart extends Mage_Checkout_Model_Cart {

    /**
     * Add product to shopping cart (quote)
     *
     * @param   int|Mage_Catalog_Model_Product $productInfo
     * @param   mixed $requestInfo
     * @return  Mage_Checkout_Model_Cart
     */
    public function addProduct($productInfo, $requestInfo = null) {
        $product = $this->_getProduct($productInfo);
        $request = $this->_getProductRequest($requestInfo);

        $productId = $product->getId();
        $flag = 0;
        $qty = 0;
        $productType = $product->getTypeId();

        if (($productType == Mage_Catalog_Model_Product_Type::TYPE_SIMPLE || $productType == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE) && ($product->getCarebyzinc() == 1)) {
            $flag = 1;
            $qty = $request->getQty() ? $request->getQty() : 1;
            $request['qty'] = 1;
        }
        
        if ($product->getStockItem()) {
            $minimumQty = $product->getStockItem()->getMinSaleQty();
            //If product was not found in cart and there is set minimal qty for it
            if ($minimumQty && $minimumQty > 0 && $request->getQty() < $minimumQty && !$this->getQuote()->hasProductId($productId)
            ) {
                $request->setQty($minimumQty);
            }
        }
        
        if ($flag) {
            if ($productId) {
                try {
                    for ($i=0; $i<$qty; $i++) {
                        $additionalOptions = array();
                        $product = Mage::getModel('catalog/product')
                                ->setStoreId(Mage::app()->getStore()->getId())
                                ->load($productId);
                        $result = $this->getQuote()->addProduct($product, $request);
                        if (is_object($result)) {

                            $result = ( $result->getParentItem() ? $result->getParentItem() : $result );

                            $carebyzincId = $request->getCarebyzincOption();
                            $priceQuote = Mage::getSingleton('core/session')->getCareQuote();
                            $carebyzincAry = $priceQuote[$carebyzincId];
                            
                            if (!empty($carebyzincAry)) {
                                $productPrice = $result->getProduct()->getFinalPrice();
                                if ($carebyzincId) {
                                    $result->setCarebyzincVariantid($carebyzincId);
                                    $warrantPrdctId = Mage::getStoreConfig('carebyzinc/general/warranty_product');
                                    if ($warrantPrdctId) {
                                        $req = $this->_getProductRequest(array('product' => $warrantPrdctId, 'qty' => 1, 'form_key' => $request['form_key']));

                                        $this->getQuote()->save();
                                        $careParentId = $result->getId();

                                        $warrantyProduct = Mage::getModel('catalog/product')->load($warrantPrdctId);
                                        $productPrice = $warrantyProduct->getPrice();
                                        if ($price = $carebyzincAry['price_per_year']) {
                                            $newPrice = $productPrice + $price;
                                        }
                                        $resultItem = $this->getQuote()->addProduct($warrantyProduct, $req);
                                        $resultItem->setCustomPrice($newPrice);
                                        $resultItem->setOriginalCustomPrice($newPrice);
                                        $resultItem->setCarebyzincPrice($price);
                                        $resultItem->setCarebyzincParentid((int) $careParentId);
                                        $resultItem->getProduct()->setIsSuperMode(true);
                                        if ($item = $priceQuote[$carebyzincId]) {
                                            $additionalOptions[] = array(
                                                'label' => 'carebyzinc',
                                                'value' => $carebyzincId,
                                            );
                                            $resultItem->setCarebyzincOption(serialize($priceQuote[$carebyzincId]));
                                        }
                                        $resultItem->addOption(array(
                                            'product_id' => $resultItem->getProductId(),
                                            'code' => 'additional_options',
                                            'value' => serialize($additionalOptions)
                                        ));
                                        $this->getQuote()->save();
                                    }
                                }
                            }
                        }
                    }
                } catch (Mage_Core_Exception $e) {
                    $this->getCheckoutSession()->setUseNotice(false);
                    $result = $e->getMessage();
                }
                /**
                 * String we can get if prepare process has error
                 */
                if (is_string($result)) {
                    $redirectUrl = ($product->hasOptionsValidationFail()) ? $product->getUrlModel()->getUrl(
                                    $product, array('_query' => array('startcustomization' => 1))
                            ) : $product->getProductUrl();
                    $this->getCheckoutSession()->setRedirectUrl($redirectUrl);
                    if ($this->getCheckoutSession()->getUseNotice() === null) {
                        $this->getCheckoutSession()->setUseNotice(true);
                    }
                    Mage::throwException($result);
                }
            } else {
                Mage::throwException(Mage::helper('checkout')->__('The product does not exist.'));
            }

            Mage::dispatchEvent('checkout_cart_product_add_after', array('quote_item' => $result, 'product' => $product));
            $this->getCheckoutSession()->setLastAddedProductId($productId);
            Mage::getSingleton('core/session')->unsCareQuote();
        } else {
            if ($productId) {
                try {
                    $result = $this->getQuote()->addProduct($product, $request);
                } catch (Mage_Core_Exception $e) {
                    $this->getCheckoutSession()->setUseNotice(false);
                    $result = $e->getMessage();
                }
                /**
                 * String we can get if prepare process has error
                 */
                if (is_string($result)) {
                    $redirectUrl = ($product->hasOptionsValidationFail()) ? $product->getUrlModel()->getUrl(
                                    $product, array('_query' => array('startcustomization' => 1))
                            ) : $product->getProductUrl();
                    $this->getCheckoutSession()->setRedirectUrl($redirectUrl);
                    if ($this->getCheckoutSession()->getUseNotice() === null) {
                        $this->getCheckoutSession()->setUseNotice(true);
                    }
                    Mage::throwException($result);
                }
            } else {
                Mage::throwException(Mage::helper('checkout')->__('The product does not exist.'));
            }

            Mage::dispatchEvent('checkout_cart_product_add_after', array('quote_item' => $result, 'product' => $product));
            $this->getCheckoutSession()->setLastAddedProductId($productId);
        }

        return $this;
    }

}
