<?php
 /**
 * ZincPlatform
 * @package    Zinc_Carebyzinc
 * @copyright  Copyright (c) 2016-2017 Zinplatform (http://www.zincplatform.com/)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */ 
class Zinc_Carebyzinc_Block_Adminhtml_Product_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id';
        $this->_blockGroup = 'carebyzinc';
        $this->_controller = 'adminhtml_product';
        $this->_updateButton('save', 'label', Mage::helper('carebyzinc')->__('Save'));
    }

    public function getHeaderText()
    {
     if( Mage::registry('carebyzinc_data') && Mage::registry('carebyzinc_data')->getId() ) {
            return Mage::helper('carebyzinc')->__("Edit Care by Zinc of  '".'%s'."'", $this->htmlEscape(Mage::registry('carebyzinc_data')->getName()));
        }
        else
             return Mage::helper('carebyzinc')->__("Edit Care by Zinc");

    }
}
