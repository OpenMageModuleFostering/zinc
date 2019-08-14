<?php
 /**
 * ZincPlatform
 * @package    Zinc_Carebyzinc
 * @copyright  Copyright (c) 2016-2017 Zinplatform (http://www.zincplatform.com/)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */ 
class Zinc_Carebyzinc_Block_Adminhtml_Product_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form();
      $this->setForm($form);
      $fieldset = $form->addFieldset('carebyzinc_form', array('legend'=>Mage::helper('carebyzinc')->__('Care By Zinc')));
	  $fieldset->addField('carebyzinc', 'select', array(
          'label'     => Mage::helper('carebyzinc')->__('Care by Zinc'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'carebyzinc',
           'values'    => array(
		  
			  array(
                  'value'     => 0,
                  'label'     => Mage::helper('carebyzinc')->__('Disable'),
				  
              ),
              array(
                  'value'     => 1,
                  'label'     => Mage::helper('carebyzinc')->__('Enable'),
              ),
          ),

      ));  
      $fieldset->addField('carebyzinc_category', 'select', array(
          'label'     => Mage::helper('carebyzinc')->__('Category'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'category',	         

          'values'    => Mage::getModel('carebyzinc/carebyzinc')->getCategoryArray(),        
          'onchange'  => "getSubcategories(this.value); function getSubcategories(selectElement){
           var reloadurl = '".   $this->getUrl('*/product/getSubcategories')."';
           if(selectElement){
      	   new Ajax.Request(reloadurl, {parameters: {  cat: selectElement},
           method: 'post',         
           onComplete: function(transport) {
		var content = JSON.parse(transport.responseText); 
		var i = 0;
		for (var key in content) {
			document.getElementById('carebyzinc_subcategory').options[i] = new Option(content[key],content[key]);
			i++;
		}
					
            }
        });
        }else
        	document.getElementById('carebyzinc_subcategory').innerHTML='';
    }",
      )); 
      $category = Mage::registry('carebyzinc_data')->getData('carebyzinc_category');
      if(($this->getRequest()->getParam('id')) && ($category)) { 
	       $fieldset->addField('carebyzinc_subcategory', 'select', array(
	          'label'     => Mage::helper('carebyzinc')->__('Sub Category'),
	          'class'     => 'required-entry',
	          'required'  => true,
	          'name'      => 'subcategory',
	          'values'    => Mage::getModel('carebyzinc/carebyzinc')->getSubCategoryArray($category),
	      )); 
      }else{
      	
      	$fieldset->addField('carebyzinc_subcategory', 'select', array(
	          'label'     => Mage::helper('carebyzinc')->__('Sub Category'),
	          'class'     => 'required-entry',
	          'required'  => true,
	          'name'      => 'subcategory',
	          'values'    => array(''=>'Please Select'),
	      )); 
      
      
      }   
     
      if ( Mage::getSingleton('adminhtml/session')->getVendorData() )
      {
         $form->setValues(Mage::getSingleton('adminhtml/session')->getCarebyzincData());
          Mage::getSingleton('adminhtml/session')->setCarebyzincData(null);
      } elseif ( Mage::registry('carebyzinc_data') ) {		
          $form->setValues( Mage::registry('carebyzinc_data')->getData());
      }
      return parent::_prepareForm();
  }
}
