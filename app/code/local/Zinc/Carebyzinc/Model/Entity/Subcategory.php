<?php
 /**
 * ZincPlatform
 * @package    Zinc_Carebyzinc
 * @copyright  Copyright (c) 2016-2017 Zinplatform (http://www.zincplatform.com/)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */  

class Zinc_Carebyzinc_Model_Entity_Subcategory extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
    public function getAllOptions()
    {
		$this->_options = array();
		if(Mage::registry('current_product')){
			if($category = Mage::registry('current_product')->getCarebyzincCategory())
				$this->_options = Mage::getModel('carebyzinc/carebyzinc')->getSubcategoryArray($category); 
        }
        return $this->_options;
    }
}
