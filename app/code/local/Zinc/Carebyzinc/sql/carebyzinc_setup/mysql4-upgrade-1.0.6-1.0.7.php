<?php
 /**
 * ZincPlatform
 * @package    Zinc_Carebyzinc
 * @copyright  Copyright (c) 2016-2017 Zinplatform (http://www.zincplatform.com/)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */ 
 
$installer = $this;

$installer->startSetup();

Mage::app()->getStore()->setId(Mage_Core_Model_App::ADMIN_STORE_ID);

$websiteIds = Mage::getModel('core/website')->getCollection()
    ->addFieldToFilter('website_id', array('neq'=>0))
    ->getAllIds();

$product = Mage::getModel('catalog/product');
$product->setWebsiteIds($websiteIds);
$product->setTypeId('virtual');
$product->addData(array(
	'sku'    => 'Care by Zinc',
	'name'    => 'Care by Zinc Insurance Policy',
    'attribute_set_id' => $product->getDefaultAttributeSetId(),
    'status' => Mage_Catalog_Model_Product_Status::STATUS_ENABLED, 
    'visibility' => Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE, 
    'weight' => 1,
    'price'    => 0.00,
    'tax_class_id' => 0, 
    'description'  => 'Represent the Care by Zinc Insurance Policy',
	'short_description' => 'Represent the Care by Zinc Insurance Policy',	
    

));

$product->setMediaGallery (array('images'=>array (), 'values'=>array ()))
		->addImageToMediaGallery('media/catalog/product/zinc/warranty.png', array('image','thumbnail','small_image'), false, false) ;
 
$product->save();

$stockItem = Mage::getModel('cataloginventory/stock_item');
$stockItem->assignProduct($product)
              ->setData('stock_id', 1)
              ->setData('store_id', 1);
              
$stockItem->setData('qty', 99999)
          ->setData('is_in_stock', 1)
          ->setData('manage_stock', 1)
          ->setData('use_config_manage_stock', 0)
          ->save();		
          																						
$installer->endSetup();

$catalogInstaller = Mage::getResourceModel('catalog/setup', 'catalog_setup'); 

$catalogInstaller->startSetup();

$catalogInstaller->updateAttribute(Mage_Catalog_Model_Product::ENTITY,'carebyzinc_subcategory','frontend_input_renderer','carebyzinc/adminhtml_catalog_product_renderer_subcategory');

$catalogInstaller->addAttribute('catalog_product', 'carebyzinc_manufacturer', array(
    'group'             => 'Zinc Admin',
    'label'             => 'Manufacturer',
    'type' 				=> 'varchar',
    'visible'           => true,
    'input'             => 'text',
    'required'          => false,
    'user_defined'      => false,
    'searchable'        => false,
    'filterable'        => false,
    'comparable'        => false,
    'visible_on_front'  => true,
    'visible_in_advanced_search' => false,
    'input_renderer' 	=> 'carebyzinc/adminhtml_catalog_product_renderer_manufacturer',
    'unique'            => false,
 //   'note'				=> 'Maximum 30 Characters',
    'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
));
$catalogInstaller->addAttribute('catalog_product', 'carebyzinc_model', array(
    'group'             => 'Zinc Admin',
    'label'             => 'Model',
    'input'				=> 'text',
    'type' 				=> 'varchar',
    'visible'           => true,
    'required'          => false,
    'user_defined'      => false,
    'searchable'        => false,
    'filterable'        => false,
    'comparable'        => false,
    'visible_on_front'  => true,
    'visible_in_advanced_search' => false,
    'input_renderer' 	=> 'carebyzinc/adminhtml_catalog_product_renderer_model',
    'unique'            => false,
  //  'note'				=> 'Maximum 30 Characters',
    'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
));



$catalogInstaller->endSetup();


																	

