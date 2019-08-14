<?php
 /**
 * ZincPlatform
 * @package    Zinc_Carebyzinc
 * @copyright  Copyright (c) 2016-2017 Zinplatform (http://www.zincplatform.com/)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */ 
class Zinc_Carebyzinc_Model_System_Config_Protocol
{
   public function toOptionArray()
    {
        return array(
            array(
                'value' => 'http',
                'label' => 'http',
            ),
            array(
                'value' => 'https',
                'label' => 'https',
            ),
        );
    }
}
