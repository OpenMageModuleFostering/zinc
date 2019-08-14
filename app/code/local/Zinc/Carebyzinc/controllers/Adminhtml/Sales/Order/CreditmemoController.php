<?php
 /**
 * ZincPlatform
 * @package    Zinc_Carebyzinc
 * @copyright  Copyright (c) 2016-2017 Zinplatform (http://www.zincplatform.com/)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */ 

/**
 * Adminhtml sales order creditmemo controller
*/


require_once(Mage::getModuleDir('controllers','Mage_Adminhtml').DS.'Sales/Order'. DS.'CreditmemoController.php');

class Zinc_Carebyzinc_Adminhtml_Sales_Order_CreditmemoController extends Mage_Adminhtml_Sales_Order_CreditmemoController
{
    /**
     * Get requested items qtys and return to stock flags
     */
    protected function _getItemData()
    {
        $data = $this->getRequest()->getParam('creditmemo');
        if (!$data) {
            $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
        }

        if (isset($data['items'])) {
			            
            if(Mage::getStoreConfig('carebyzinc/general/enabled')){
				foreach($data['items'] as $key=>$value){
					$orderItem = Mage::getModel('sales/order_item')->load($key);
					if($orderItem->getProductId() == Mage::getStoreConfig('carebyzinc/general/warranty_product')){
						if($data['items'][$key]['qty']==0){
							if($data['items'][$orderItem->getCarebyzincParentid()]['qty'] == 0){}
							else{
								$data['items'][$key]['qty'] = 1;
							}
									
						}	
					}
				}					
			}
			$qtys = $data['items'];
			
        } else {
            $qtys = array();
        }
        return $qtys;
    }
  
}
