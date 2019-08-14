<?php
 /**
 * ZincPlatform
 * @package    Zinc_Carebyzinc
 * @copyright  Copyright (c) 2016-2017 Zinplatform (http://www.zincplatform.com/)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */ 
class Zinc_Carebyzinc_Adminhtml_CarebyzincController extends Mage_Adminhtml_Controller_action
{
 public function validateAction()
    {
        $data['uid'] = Mage::getStoreConfig('carebyzinc/api/user_id');
        $data['access-token'] = Mage::getStoreConfig('carebyzinc/api/access_token');
        $data['client'] = Mage::getStoreConfig('carebyzinc/api/client');      
       
 	$model = Mage::getModel('carebyzinc/carebyzinc');
  	$result = $model->callApi($data,'auth/validate_token','get');
  	if($result['code'] == 200)
  		$response = 'Success';
  	else
  		$response = 'Error';
        Mage::app()->getResponse()->setBody($response);
    }
	
}
