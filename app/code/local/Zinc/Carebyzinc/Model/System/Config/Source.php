<?php
 /**
 * ZincPlatform
 * @package    Zinc_Carebyzinc
 * @copyright  Copyright (c) 2016-2017 Zinplatform (http://www.zincplatform.com/)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */ 
class Zinc_Carebyzinc_Model_System_Config_Source
{
    public function toOptionArray()
    {
        return array(
            array(
                'value' => 'test',
                'label' => 'Regression',
            ),
            array(
                'value' => 'sandbox',
                'label' => 'Sandbox',
            ),
            array(
                'value' => 'staging',
                'label' => 'Staging',
            ),
            array(
                'value' => 'live',
                'label' => 'Production',
            ),
        );
    }
}
