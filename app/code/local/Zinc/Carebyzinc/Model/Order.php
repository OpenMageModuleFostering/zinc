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
		if((Mage::getStoreConfig('carebyzinc/api/testmode')) == 'live') {
            $mode = 1;
        } else {
            $mode = 0;
        }

		$orderId = $order->getId();
		foreach($orderItems as $item) {
			if($item->getCarebyzincVariantid()) {
				$policyNo = '';	
                $carezincOption = '';	
				$orderItemCollection = Mage::getModel('sales/order_item')->getCollection()
                    ->addFieldToFilter('carebyzinc_parentid', $item->getQuoteItemId())
                    ->addFieldToFilter('order_id', $orderId);
							
				foreach($orderItemCollection as $col) {
                    $carezincOption = $col->getCarebyzincOption();				
				}
                
				$carebyzincAry = (array) unserialize($carezincOption);
				$product = Mage::getModel('catalog/product')->load($item->getProductId());
				$data['price_quote_id'] = $carebyzincAry['id'];				
				$data['sku_id'] = $carebyzincAry['sku_id'];				
				$result = $model->callApi($data, 'policies');
				$policyAry = $result['response'];
				if($result['code'] == 200){
					$policyArray = (array) json_decode($policyAry);				
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
				$careOrder->setProductPrice($item->getPrice());
				$careOrder->setCarebyzincProvider($carebyzincAry['provider']);
				$name = $order->getCustomerFirstname(). ' '. $order->getCustomerLastname();
				$careOrder->setCustomerName($name);
				$careOrder->setCustomerEmail($order->getCustomerEmail());
				$careOrder->setCreatedTime(now());
				$careOrder->setOrderCreatedMode($mode);
				$careOrder->save();
			}			
		}
		if($carebyItem) {
			$fromname = Mage::getStoreConfig('trans_email/ident_general/name'); 
			$fromemail = Mage::getStoreConfig('trans_email/ident_general/email');
			$translate  = Mage::getSingleton('core/translate');
			$email = 'support@zincplatform.com';
			$name = 'Zinc';
			$templateId = Mage::getStoreConfig('sales_email/order/template');
			if($email) {
				for($i =0;$i<count($policyKeyArray);$i++) {
				    $storeObj = Mage::getModel('core/store')->load($order->getStoreId());
				    $anyDate = $order->getCreatedAt();
					$dateTimestamp = Mage::getModel('core/date')->timestamp(strtotime($anyDate));   
					$date = date("Y-m-d",$dateTimestamp);
					$subject = $policyKeyArray[$i].'_'.$storeObj->getFrontendName().'_'.$date;	  		
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
            "billing_state" => $billingAddress->getRegion()
        );
        
        $billingAddr = $billingAddress->getStreet();
        if(is_array($billingAddr) && isset($billingAddr[0])) {
            $address['address']["billing_address1"] = $billingAddr[0];
        } else {
            $address['address']["billing_address1"] = $billingAddr;
        }
        
		return $address;
	}
    
    public function recordOrderToZinc($order)
    {
        $result = false;
        
        $model = Mage::getModel('carebyzinc/carebyzinc');
        
        $data = array();
        $data['order_id'] = $order->getId();
        
        $anyDate = $order->getCreatedAt();
        $dateTimestamp = Mage::getModel('core/date')->timestamp(strtotime($anyDate));   
        $date = date("Y-m-d H:i:s",$dateTimestamp);
        $data['fulfilled_at'] = $date;
        
        $data['customer'] = $this->getUserAddress($order);
        
        $data['skus'] = array();
        $quote = Mage::getModel('checkout/cart')->getQuote();
        $data['skus'] = $this->_prepareProducts($order, $quote, 'cart');
        
        $outData = $model->callApi($data, 'orders');
        $response = $outData['response'];
        
        switch($outData['code']) {
            case '200':
                $quoteData = json_decode($response, true);
                $result['code'] = '200';
                break;
            default:
                $quoteData = (array) json_decode($response, true);
                $result['code'] = '400';
                if(isset($quoteData['errors'])) {
                    $result['title'] = $quoteData['errors'][0]['title'];
                    $result['details'] = $quoteData['errors'][0]['details'];
                } else {
                    $result = 'Unexpected error occured';
                }
                break;
        }
        
        return $result;
    }
    
    /**
     * Prepare thank you page after successful purchase.
     * 
     * @param object $quote
     * @param object $order
     * @return string $result - success page URL / error
     */
    public function prepareSuccessPage($quote, $order)
    {
        $result = false;
        
        $model = Mage::getModel('carebyzinc/carebyzinc');
        
        $data = array();
        $data['customer'] = $this->getUserAddress($order);
        
        $data['zip_code'] = $data['customer']['address']['billing_zip_code'];
        $data['order_id'] = $order->getId();
        
        $data['products'] = $this->_prepareProducts($order, $quote, 'success');
         
        $outData = $model->callApi($data, 'retarget/flow');
        $response = $outData['response'];
        
        switch($outData['code']) {
            case '200':
                $quoteData = json_decode($response, true);
                $result['code'] = '200';
                $result['url'] = $quoteData['url'];
                break;
            default:
                $quoteData = (array) json_decode($response, true);
                $result['code'] = '400';
                if(isset($quoteData['errors'])) {
                    $result['title'] = $quoteData['errors'][0]['title'];
                    $result['details'] = $quoteData['errors'][0]['details'];
                } else {
                    $result = 'Unexpected error occured';
                }
                break;
        }
        
        return $result;
    }
    
    /**
     * The Interstital API will provide you with an HTML snippet for a given category that you can show to your customers to add an extra explanation of what Zinc is offering
     * 
     * @param string $category - product category
     * @return array $result - html for layer / error
     */
    public function prepareInterstitials($category)
    {
        $result = false;
        
        if($category) {
            $model = Mage::getModel('carebyzinc/carebyzinc');
            
            $outData = $model->callApi($category, 'interstitials', 'get');
            $response = $outData['response'];
            
            switch($outData['code']) {
                case '200':
                    $quoteData = json_decode($response, true);
                    $result['code'] = '200';
                    $result['html'] = $quoteData['html'];
                    $result['stylesheet'] = $quoteData['stylesheet'];
                    break;
                default:
                    $quoteData = (array) json_decode($response, true);
                    $result['code'] = '400';
                    if(isset($quoteData['errors'])) {
                        $result['title'] = $quoteData['errors'][0]['title'];
                        $result['details'] = $quoteData['errors'][0]['details'];
                    } else {
                        $result = 'Unexpected error occured';
                    }
                    break;
            }
        }
        
        return $result;
    }
    
    /**
     * 
     * @param object $product - product object
     * @return string $result - interstital price
     */
    public function getInterstitalPrice($product)
    {
        $result = false;
        
        $model = Mage::getModel('carebyzinc/carebyzinc');
        
        $helper = Mage::helper('carebyzinc');
        $zip = $helper->getZipCode();
        
        $price = $model->getPriceQuote($product, $zip, false, 'INTERSTICIAL');
        if(is_array($price) && $price['code'] == '200') {
            foreach($price['price'] as $val) {
                $result = '$' . $val['price_per_year'];
            }
        }
        
        return $result;
    }
    
    /**
     * Import historical orders from admin panel
     * 
     * @return array $result - success / failure
     */
    public function massImportOrders()
    {
        $result = false;
        
        $result['code'] == 200;
        
        return $result;
    }
    
    /**
     * Process purchase insurance on product detail page
     * 
     * @param array $post - data send from the form
     * @return json $result - redirection url
     */
    public function processPurchaseInsurance($post)
    {
        $result = false;
        
        if(is_array($post)) {
            $pId = (int)$post['pId'];
            $product = Mage::getModel('catalog/product')->load($pId);
            if(isset($post['action'])) {
                switch($post['action']) {
                    case 'add':
                        if(Mage::getStoreConfig('checkout/cart/redirect_to_cart')) {
                            $result['url'] = Mage::getUrl("checkout/cart/");
                            $result['action'] = 'add';
                        }
                        break;
                    case 'cancel':
                    default:
                        if(Mage::getStoreConfig('checkout/cart/redirect_to_cart')) {
                            $result['url'] = Mage::getUrl("checkout/cart/");
                            $result['action'] = 'cancel';
                        }
                        break;
                }
            }
        }
        
        return $result;
    }

    protected function _prepareProducts($order, $quote = false, $type = 'cart')
    {
        $result = false;
        
        $warrantyProductId = (int)Mage::getStoreConfig('carebyzinc/general/warranty_product');
        $orderItems = $order->getAllVisibleItems();
        
        $i = 0;
        foreach($orderItems as $item) {
            $product = Mage::getModel('catalog/product')->load($item->getProductId());
            
            if($type == 'cart') {
                if($product->getCarebyzinc() == '1') {
                    $result[$i]['sku_name'] = $product->getSku();
                    $result[$i]['category'] = $product->getCarebyzincCategory();
                    $result[$i]['subcategory'] = $product->getCarebyzincSubcategory();
                    $result[$i]['description'] = $product->getShortDescription();
                    $result[$i]['price'] = $product->getPrice();
                    $result[$i]['currency'] = $order->getOrderCurrencyCode();
                    $result[$i]['brand'] = $product->getAttributeText('manufacturer');
                    $result[$i]['remote_image_url'] = Mage::getUrl('media') . 'catalog/product' . $product->getImage();
                    $i++;
                }
            } elseif($type == 'success') {
                if($product->getCarebyzinc() == '1' && $item->getCarebyzincVariantid() == null) {
                    $result[$i]['sku_name'] = $product->getSku();
                    $result[$i]['category'] = $product->getCarebyzincCategory();
                    $result[$i]['subcategory'] = $product->getCarebyzincSubcategory();
                    $result[$i]['description'] = $product->getShortDescription();
                    $result[$i]['price'] = $product->getPrice();
                    $result[$i]['currency'] = $order->getOrderCurrencyCode();
                    $result[$i]['brand'] = $product->getAttributeText('manufacturer');
                    $result[$i]['remote_image_url'] = Mage::getUrl('media') . 'catalog/product' . $product->getImage();
                    $i++;
                }
            }
        }
        
        if($quote && $type == 'cart') {
            foreach ($quote->getAllVisibleItems() as $item) {
                if($warrantyProductId == (int)$item->getProductId()) {
                    $product = Mage::getModel('catalog/product')->load($item->getProductId());
                    $result[$i]['sku_name'] = $item->getProduct()->getName();
                    $result[$i]['category'] = 'Insurance';
                    $result[$i]['subcategory'] = 'Insurance';
                    $result[$i]['description'] = $product->getShortDescription();
                    $result[$i]['price'] = $item->getPrice();
                    $result[$i]['currency'] = $order->getOrderCurrencyCode();
                    $result[$i]['brand'] = 'NA';
                    $result[$i]['remote_image_url'] = 'NA';
                    $i++;
                }
            }
        }
        
        return $result;
    }

    static public function getOptionArray()
    {
        return array(
            self::PRODUCTION => Mage::helper('carebyzinc')->__('Production'),
            self::SANDBOX    => Mage::helper('carebyzinc')->__('Sandbox')
        );
    }	
}
