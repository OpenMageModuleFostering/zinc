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
          'label'     => Mage::helper('carebyzinc')->__('Enable Zinc Quote'),
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
          'label'     => Mage::helper('carebyzinc')->__('Zinc Category'),
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
			document.getElementById('carebyzinc_subcategory').options.length = 0;
		for (var key in content) {
			document.getElementById('carebyzinc_subcategory').options[i] = new Option(content[key],key);
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
	          'label'     => Mage::helper('carebyzinc')->__('Zinc Subcategory'),
	          'class'     => 'required-entry',
	          'required'  => true,
	          'name'      => 'subcategory',
	          'values'    => Mage::getModel('carebyzinc/carebyzinc')->getSubCategoryArray($category),
	      )); 
      }else{
      	
      	$fieldset->addField('carebyzinc_subcategory', 'select', array(
	          'label'     => Mage::helper('carebyzinc')->__('Zinc Subcategory'),
	          'class'     => 'required-entry',
	          'required'  => true,
	          'name'      => 'subcategory',
	          'values'    => array(''=>'Please Select'),
	      )); 
      
      
      }   
      $fieldset->addField('carebyzinc_manufacturer', 'text', array(
	          'label'     => Mage::helper('carebyzinc')->__('Manufacturer'),
	          'class'     => 'required-entry  validate-length maximum-length-30 ',
	          'required'  => true,
	          'name'      => 'carebyzinc_manufacturer',
	   )); 
	  $fieldset->addField('carebyzinc_model', 'text', array(
	          'label'     => Mage::helper('carebyzinc')->__('Model'),
	          'class'     => 'required-entry  validate-length maximum-length-30 ',
	          'required'  => true,
	          'name'      => 'carebyzinc_model',
	  )); 
      
     $this->setChild('form_after', $this->getLayout()
        ->createBlock('adminhtml/widget_form_element_dependence')
        ->addFieldMap('carebyzinc', 'carebyzinc')
        ->addFieldMap('carebyzinc_category', 'carebyzinc_category')
        ->addFieldMap('carebyzinc_manufacturer', 'carebyzinc_manufacturer')
        ->addFieldMap('carebyzinc_model', 'carebyzinc_model')
        ->addFieldMap('carebyzinc_subcategory', 'carebyzinc_subcategory')
        ->addFieldDependence('carebyzinc_category', 'carebyzinc', 1) 
        ->addFieldDependence('carebyzinc_subcategory', 'carebyzinc', 1) 
        ->addFieldDependence('carebyzinc_model', 'carebyzinc_category', array((string)'Bicycle',(string)'Electronics')) 
        ->addFieldDependence('carebyzinc_manufacturer', 'carebyzinc_category', array((string)'Bicycle',(string)'Electronics')) 
	  );
     
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
