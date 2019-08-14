<?php
 /**
 * ZincPlatform
 * @package    Zinc_Carebyzinc
 * @copyright  Copyright (c) 2016-2017 Zinplatform (http://www.zincplatform.com/)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */ 
class Zinc_Carebyzinc_Block_Adminhtml_Product_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

  public function __construct()
  {
      parent::__construct();
      $this->setId('product_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle(Mage::helper('carebyzinc')->__('Care by Zinc'));
  }

  protected function _beforeToHtml()
  {
      $this->addTab('form_section', array(
          'label'     => Mage::helper('carebyzinc')->__('Care by Zinc'),
          'title'     => Mage::helper('carebyzinc')->__('Care by Zinc'),
          'content'   => $this->getLayout()->createBlock('carebyzinc/adminhtml_product_edit_tab_form')->toHtml(),
      ));
      
      return parent::_beforeToHtml();
  }
}
