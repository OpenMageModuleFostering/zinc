<?php
 /**
 * ZincPlatform
 * @package    Zinc_Carebyzinc
 * @copyright  Copyright (c) 2016-2017 Zinplatform (http://www.zincplatform.com/)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */ 
class Zinc_Carebyzinc_Model_Observer{
	
	
		
	public function saveCarebyzinc($observer)
	{
		if(!Mage::registry('carebyzinc_save')):
			$order = $observer->getEvent()->getOrder();
			$model = Mage::getModel('carebyzinc/order');
			$model->savePolicy($order);
			Mage::register('carebyzinc_save',true);
		endif;
		
	}
	public function cartLoad($observer)
	{
		if( ! Mage::getStoreConfig('carebyzinc/general/enabled')){
			$cartHelper = Mage::helper('checkout/cart');
			$cart = Mage::getModel('checkout/cart')->getQuote();
			foreach ($cart->getAllItems() as $item) {
			    if($item->getCarebyzincOption()){
			       $cartHelper->getCart()->removeItem($item->getId())->save();
			    }			   
				$additionalOptions = array();
				$item->addOption(array(
									'product_id' => $item->getProductId(),
									'code' => 'additional_options',
									'value' => serialize($additionalOptions)
								));
				$item->save();
			}
		

		}
		
	}
	

	
}
