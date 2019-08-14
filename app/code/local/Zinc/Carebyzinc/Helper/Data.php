<?php
/**
 * ZincPlatform
 * @package    Zinc_Carebyzinc
 * @copyright  Copyright (c) 2016-2017 Zinplatform (http://www.zincplatform.com/)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Zinc_Carebyzinc_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function getZipCode()
    {
        $zip = '';
        if(Mage::getSingleton('customer/session')->isLoggedIn()) {
            $primaryAddress = Mage::getSingleton('customer/session')->getCustomer()->getPrimaryShippingAddress();
            if($primaryAddress) {
                if($primaryAddress->getPostcode()) {
                    $zip = $primaryAddress->getPostcode();
                }
            }
        }
        
        if(!$zip) {
            $url = 'http://freegeoip.net/json/' . $_SERVER['REMOTE_ADDR'];
            $response = $this->callApi($url);
            $responseArray = (array) json_decode($response);
            $zip = $responseArray['zip_code'];
            if (!$zip) {
                $zip = Mage::getStoreConfig('carebyzinc/general/defaultzip');
            }
        }
        
        return $zip;
    }

    public function getDeleteUrl($itemId) 
    {
        return Mage::getUrl('carebyzinc/index/removeWarranty', array('id' => $itemId));
    }

    public function callApi($url)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }

    /**
     * Check quote visability on frontend (for product and cart pages)
     * 
     * @param string $type - page type
     * @return boolean $result - show/hide care by zinc section
     */
    public function checkQuoteVisibility($type = 'product') 
    {
        $result = false;
        switch($type) {
            case 'cart':
                $result = Mage::getStoreConfig('carebyzinc/display/quotecart');
                break;
            case 'product':
                $result = Mage::getStoreConfig('carebyzinc/display/quoteproduct');
                break;
            default:
                break;
        }
        
        return $result;
    }
    
    /**
     * Check interstital visability on frontend (product details page) 
     * 
     * @return boolean $result 
     */
    public function checkInterstitalVisibility()
    {
        $result = false;
        
        $result = Mage::getStoreConfig('carebyzinc/display/interstitial');
        
        return $result;
    }
    
    /**
     * Check thank you popup visability on frontend (after successful order) 
     * 
     * @return boolean $result 
     */
    public function checkThankYouVisibility()
    {
        $result = false;
        
        $result = Mage::getStoreConfig('carebyzinc/display/thankyou');
        
        return $result;
    }
}
