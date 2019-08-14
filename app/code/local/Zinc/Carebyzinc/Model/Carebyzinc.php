<?php

/**
 * ZincPlatform
 * @package    Zinc_Carebyzinc
 * @copyright  Copyright (c) 2016-2017 Zinplatform (http://www.zincplatform.com/)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Zinc_Carebyzinc_Model_Carebyzinc extends Mage_Core_Model_Abstract
{

    const WARRANTY_ENABLED  = 1;
    const WARRANTY_DISABLED = 0;
    
    const QUOTE_ADDITIONAL_PARAMS_SOURCE_PRODUCT = 'PRODUCT_PROFILE';
    const QUOTE_ADDITIONAL_PARAMS_SOURCE_CART    = 'CART';

    public function _construct()
    {
        parent::_construct();
        $this->_init('carebyzinc/carebyzinc');
    }
    
    /**
     * Get Zinc Plugin enablement status
     * 
     * @return string Enabled/Disabled
     */
    static public function getOptionArray()
    {
        return array(
            self::WARRANTY_ENABLED  => Mage::helper('carebyzinc')->__('Enabled'),
            self::WARRANTY_DISABLED => Mage::helper('carebyzinc')->__('Disabled')
        );
    }
    
    /**
     * Get Zinc Quote for product page
     * 
     * @param object $product
     * @param string $zip
     * @param string $optionPrice
     * @param string $source
     * 
     * @return mixed $result - price quoite / error message
     */
    public function getPriceQuote($product, $zip, $optionPrice, $source = self::QUOTE_ADDITIONAL_PARAMS_SOURCE_PRODUCT)
    {
        $result = false;
        
        $currencyCode = Mage::app()->getStore()->getBaseCurrencyCode();
        $price = $product->getFinalPrice();
        if ($optionPrice) {
            $price += $optionPrice;
        }
       
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
            'remote_image_url' => Mage::getUrl('media') . 'catalog/product' . $product->getImage()
        );
        
        $data['additional_params'] = array(
            'source' => $source
        );
        
        $catArray = array('bicycle', 'electronics');
        if (in_array(strtolower($product->getCarebyzincCategory()), $catArray)) {

            if (($product->getCarebyzincModel()) && ($product->getCarebyzincManufacturer())) {
                $additional = array('model' => $product->getCarebyzincModel());
                $data['sku']['additional_info'] = json_encode($additional);
                $data['sku']['brand'] = $product->getCarebyzincManufacturer();
            }
        }

        if (!$zip) {
            $helper = Mage::helper('carebyzinc');
            $zip = $helper->getZipCode();
        }
        
        $data['zip_code'] = $zip;
        
        $outData = $this->callApi($data, 'price_quotes/generate');
        $response = $outData['response'];
        
        switch($outData['code']) {
            case '200':
                $quoteData = json_decode($response, true);
                $priceQuote = array();
                foreach ($quoteData['price_quotes'] as $item) {
                    $priceQuote[$item['id']] = $item;
                }
                Mage::getSingleton('core/session')->setCareQuote($priceQuote);
                $result['code'] = '200';
                $result['price'] = $priceQuote;
                break;
            case '400':
                $quoteData = (array) json_decode($response, true);
                $result['code'] = '400';
                if(isset($quoteData['errors'])) {
                    $result['title'] = $quoteData['errors'][0]['title'];
                    $result['details'] = $quoteData['errors'][0]['details'];
                } else {
                    $result = 'No Quotes Available';
                }
                break;
        }
        
        return $result;
    }
    
    /**
     * Get Zinc Quote for cart page
     * 
     * @param object $product
     * @param string $zip
     * @param string $optionPrice
     * 
     * @return mixed $result - price quoite / error message
     */
    public function getPriceQuoteinCart($product, $itemId, $zip) 
    {    
        $result = false;
        
        $currencyCode = Mage::app()->getStore()->getBaseCurrencyCode();
        $_item = Mage::getModel('sales/quote_item')->load($itemId);
        
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
            'remote_image_url' => Mage::getUrl('media') . 'catalog/product' . $product->getImage()
        );
        
        $data['additional_params'] = array(
            'source' => self::QUOTE_ADDITIONAL_PARAMS_SOURCE_CART
        );
        
        $catArray = array('bicycle', 'electronics');
        if (in_array(strtolower($product->getCarebyzincCategory()), $catArray)) {
            if (($product->getCarebyzincModel()) && ($product->getCarebyzincManufacturer())) {
                $additional = array('model' => $product->getCarebyzincModel());
                $data['sku']['additional_info'] = json_encode($additional);
                $data['sku']['brand'] = $product->getCarebyzincManufacturer();
            }
        }
        
        if (!$zip) {
            $helper = Mage::helper('carebyzinc');
            $zip = $helper->getZipCode();
        }
        
        $data['zip_code'] = $zip;
        
        $outData = $this->callApi($data, 'price_quotes/generate');
        $response = $outData['response'];
        
        switch($outData['code']) {
            case '200':
                $quoteData = json_decode($response, true);
                $priceQuote = array();
                if (!empty($quoteData['price_quotes'])) {
                    $priceQuote = Mage::getSingleton('core/session')->getCareByZincQuote();
                    foreach ($quoteData['price_quotes'] as $item) {   
                        if ($priceQuote[$itemId]) {
                            unset($priceQuote[$itemId]);
                        }
                        $priceQuote[$itemId][$item['id']] = $item;
                    }
                    
                    Mage::getSingleton('core/session')->setCareByZincQuote($priceQuote);
                    $result['code'] = '200';
                    $result['price'] = $priceQuote;
                } else {
                    return 'No Quotes Available';
                }
                break;
            case '400':
                $quoteData = (array) json_decode($response, true);
                $result['code'] = '400';
                if(isset($quoteData['errors'])) {
                    $result['title'] = $quoteData['errors'][0]['title'];
                    $result['details'] = $quoteData['errors'][0]['details'];
                } else {
                    $result = 'No Quotes Available';
                }
                break;
        }
        
        return $result;
    }

    /**
     * Get API URL based on selected application mode
     * 
     * @param type $action
     * 
     * @return string API URL
     */
    public function getApiUrl($action, $params = false)
    {
        $path = Mage::getStoreConfig('carebyzinc/api/url');

        switch (Mage::getStoreConfig('carebyzinc/api/testmode')) {
            case 'live':
                $path = Mage::getStoreConfig('carebyzinc/api/url');
                break;
            case 'sandbox':
                $path = Mage::getStoreConfig('carebyzinc/api/sandbox_url');
                break;
            case 'staging':
                $path = Mage::getStoreConfig('carebyzinc/api/staging_url');
                break;
            case 'test':
            default:
                $path = Mage::getStoreConfig('carebyzinc/api/test_url');
                break;
        }

        $protocol = 'https://';
        $url = $protocol . $path . '/' . $action;
        
        if($params) {
            $url .= '/' . $params;
        }
        
        return $url;
    }

    /*
     * Get Token based on selected application mode
     * 
     * @return string User Token
     */
    public function getToken()
    {
        $token = false;
        switch (Mage::getStoreConfig('carebyzinc/api/testmode')) {
            case 'live':
                $token = Mage::getStoreConfig('carebyzinc/api/xuser_token');
                break;
            case 'sandbox':
                $token = Mage::getStoreConfig('carebyzinc/api/sandbox_xuser_token');
                break;
            case 'staging':
                $token = Mage::getStoreConfig('carebyzinc/api/staging_xuser_token');
                break;
            case 'test':
            default:
                $token = Mage::getStoreConfig('carebyzinc/api/test_xuser_token');
                break;
        }

        return $token;
    }
    
    /**
     * Call Zinc API
     * 
     * @param array $data - input data
     * @param string $action
     * @param string $method
     * 
     * @return json $result - API response
     */
    public function callApi($data, $action, $method = 'post')
    {
        $result = array();
        $urlParams = false;
        
        if($action == 'interstitials') {
            $urlParams = $data;
        } else {
            $values = json_encode($data);
        }
        
        $url = $this->getApiUrl($action, $urlParams);
        
        $token = $this->getToken();
        $email = Mage::getStoreConfig('carebyzinc/api/xuser_email');
        
        if (($action == 'price_quotes/generate') || ($action == 'policies' ) || ($action == 'retarget/flow') || ($action == 'orders') || ($action == 'interstitials')) {
            $header = array("Content-Type: application/json", "X-User-Token:$token", "X-User-Email:$email", "token-type:Bearer");
        } elseif ($action == 'token') {
            $header = array("Content-Type: application/json", "X-User-Token:$token", "X-User-Email:$email", "token-type:Bearer");
        } else {
            $header = array("Content-Type: application/json", "token-type:Bearer");
        }
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_URL, $url);
        if ($method == 'post') {
            curl_setopt($ch, CURLOPT_POST, TRUE);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $values);
        }
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $result['response'] = curl_exec($ch);
        $result['code'] = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        return $result;
    }

    /**
     * Get warranty status
     * 
     * @param type $itemId
     * @param type $warrentyitem
     * @return boolean|string
     */
    public function getwarrantyStatus($itemId, $warrantyItem)
    {
        if ($item_id < 0) {
            return '';
        }
        
        $cart = Mage::getModel('checkout/cart');
        $quoteItem = $cart->getQuote()->getItemById($itemId);
        if ($quoteItem) {
            $product = Mage::getModel('catalog/product')->load($quoteItem->getProductId());
            if (($product->getCarebyzinc() != 1) && ($quoteItem->getCarebyzincVariantid())) {

                $quoteItem->setCarebyzincVariantid(NULL);
                $quoteItem->save();
                $cartHelper = Mage::helper('checkout/cart');
                $cartHelper->getCart()->removeItem($warrantyItem)->save();
                $cart->getQuote()->collectTotals()->save();
            }
        }
        
        return true;
    }

    /**
     * Get Category list
     * 
     * @return array $result - category list
     */
    public function getCategoryArray()
    {
        $dataArray = $this->getCategoryJson();
        $category = array('' => 'Please Select');
        foreach ($dataArray as $key => $value) {
            $category[$key] = $key;
        }
        
        return $category;
    }

    /**
     * Get Subcategory list
     * 
     * @return array $result - subcategory list
     */
    public function getSubCategoryArray($category) 
    {
        if ($category)
            $dataArray = $this->getCategoryJson();
        $subcategory = array('' => 'Please Select');
        foreach ($dataArray as $key => $value) {
            if ($key == $category) {
                foreach ($value as $val) {
                    $subcategory[$val] = $val;
                }
            }
        }
        return $subcategory;
    }
    
    /**
     * Get Subcategory in json
     * 
     * @return array $result - category list
     */
    public function getCategoryJson()
    {
        $result = false;
        
        $outData = $this->callApi('', 'categories', 'get');
        if ($outData['code'] == '200') {
            $response = $outData['response'];
        } else {
            $response = '{"Jewelry": ["Bracelet", "Necklace","Pendant","Brooch","Engagement Ring","Wedding Ring","Other Ring","Other"]}';
        }
        $result = json_decode($response);
        
        return $result;
    }
    
    /**
     * Get subcategory suggestions
     * 
     * @param string $category - category name
     * @return array $result -subcategories suggestion
     */
    public function getSubCategorySuggestions($category)
    {
        $result = false;
        
        $outData = $this->callApi('', 'category_suggestions', 'get');
        if ($outData['code'] == '200'){    
            $response = $outData['response'];
            $decode = json_decode($response);
            $result = $decode->$category;
        }
        
        return $result;
    }
    
    /**
     *  Get warranty name
     * 
     * @param int $itemId
     * @return string $result - warranty name
     */
    public function getWarrantyName($itemId)
    {
        $result = '';

        $orderItem = Mage::getModel('sales/quote_item')->load($itemId);
        $product = Mage::getModel('catalog/product')->load($orderItem->getProductId());
        if ($product) {
            $result = ' for ' . $product->getName();
        }
        
        return $result;
    }
    
    public function prepareZincQuoteLayer($product)
    {
        
    }
}
