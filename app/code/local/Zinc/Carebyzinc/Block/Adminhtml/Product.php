<?php
 /**
 * ZincPlatform
 * @package    Zinc_Carebyzinc
 * @copyright  Copyright (c) 2016-2017 Zinplatform (http://www.zincplatform.com/)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */ 
class Zinc_Carebyzinc_Block_Adminhtml_Product extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_product';
    $this->_blockGroup = 'carebyzinc';
    $this->_headerText = Mage::helper('carebyzinc')->__('Care by Zinc Manager');
    parent::__construct();
	$this->_removeButton('add');
  }
}
