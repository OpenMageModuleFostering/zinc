<?php
 /**
 * ZincPlatform
 * @package    Zinc_Carebyzinc
 * @copyright  Copyright (c) 2016-2017 Zinplatform (http://www.zincplatform.com/)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */  

class Zinc_Carebyzinc_Block_Adminhtml_Catalog_Product_Renderer_List extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    { 
        $result = '';
        $categoryCache = array();
        
        $value =  $row->getData($this->getColumn()->getIndex());
        if(!empty($value)) {
            $value = explode(",", $value);
            foreach($value as $val) {
                if(in_array($val, $categoryCache)) {
                    $result .= $categoryCache[$val] . '<br />';
                } else {
                    $_category = Mage::getModel('catalog/category')->load($val);
                    $categoryName = $_category->getName();
                    $categoryCache[$val] = $_category->getName();
                    $result .= $categoryName . '<br />';
                }
            }
        }

        return $result;
    }
}