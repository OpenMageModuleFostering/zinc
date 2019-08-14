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
        $model = Mage::getModel('carebyzinc/carebyzinc');
        $data['X-User-Token'] = $model->getToken();
        $data['X-User-Email'] = Mage::getStoreConfig('carebyzinc/api/xuser_email');

        $result = $model->callApi($data, 'token', 'post');
        if ($result['code'] == 200) {
            $response = 'Success';
        } else {
            $response = 'Error';
        }

        Mage::app()->getResponse()->setBody($response);
    }
    
    public function importordersAction()
    {
        $model = Mage::getModel('carebyzinc/order');
        $result = $model->massImportOrders();
        
        if ($result['code'] == 200) {
            $response = 'Success';
        } else {
            $response = 'Error';
        }

        Mage::app()->getResponse()->setBody($response);
    }
}
