<?php
 /**
 * ZincPlatform
 * @package    Zinc_Carebyzinc
 * @copyright  Copyright (c) 2016-2017 Zinplatform (http://www.zincplatform.com/)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */ 
 

require_once 'Mage/Checkout/controllers/OnepageController.php';
class Zinc_Carebyzinc_Checkout_OnepageController extends Mage_Checkout_OnepageController
{
    /**
     * Order success action
     */
    public function successAction()
    {
        $session = $this->getOnepage()->getCheckout();
        if (!$session->getLastSuccessQuoteId()) {
            $this->_redirect('checkout/cart');
            return;
        }

        $lastQuoteId = $session->getLastQuoteId();
        $lastOrderId = $session->getLastOrderId();
        
        $lastRecurringProfiles = $session->getLastRecurringProfileIds();
        if (!$lastQuoteId || (!$lastOrderId && empty($lastRecurringProfiles))) {
            $this->_redirect('checkout/cart');
            return;
        }
        
        $thankyou = Mage::getStoreConfig('carebyzinc/display/thankyou');
        if($thankyou == '1') {
            $model = Mage::getModel('carebyzinc/order');
            $quote = Mage::getModel('sales/quote')->load($lastQuoteId);
            $order = Mage::getModel('sales/order')->load($lastOrderId);

            $success = $model->prepareSuccessPage($quote, $order);
            Mage::register('zincSuccess', $success);
        }
        
        $session->clear();
        $this->loadLayout();
        $this->_initLayoutMessages('checkout/session');
        Mage::dispatchEvent('checkout_onepage_controller_success_action', array('order_ids' => array($lastOrderId)));
        $this->renderLayout();
    }
}
