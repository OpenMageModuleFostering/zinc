<?php
 /**
 * ZincPlatform
 * @package    Zinc_Carebyzinc
 * @copyright  Copyright (c) 2016-2017 Zinplatform (http://www.zincplatform.com/)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */ 
$setup = new Mage_Eav_Model_Entity_Setup('core_setup');
$setup->addAttributeGroup('catalog_product', 'Default', 'Zinc Admin', 1000);
$installer = Mage::getResourceModel('catalog/setup', 'catalog_setup'); 
$installer->startSetup();

$installer->addAttribute('catalog_product', 'carebyzinc_category', array(
    'group'             => 'Zinc Admin',
    'label'             => 'Care by Zinc Category',
    'type' 		=> 'varchar',
    'visible'           => true,
    'input'             => 'select',
    'required'          => false,
    'user_defined'      => false,
    'searchable'        => false,
    'filterable'        => false,
    'comparable'        => false,
    'visible_on_front'  => true,
    'visible_in_advanced_search' => false,
    'unique'            => false,
    'source'		=>'carebyzinc/entity_category',
    'input_renderer' 	=> 'carebyzinc/adminhtml_catalog_product_renderer_category',
    'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
));
$installer->addAttribute('catalog_product', 'carebyzinc_subcategory', array(
    'group'             => 'Zinc Admin',
    'label'             => 'Care by Zinc Subcategory',
    'input'		=> 'select',
    'type' 		=> 'varchar',
    'visible'           => true,
    'required'          => false,
    'user_defined'      => false,
    'searchable'        => false,
    'filterable'        => false,
    'comparable'        => false,
    'visible_on_front'  => true,
    'visible_in_advanced_search' => false,
    'unique'            => false,
    'source'		=>'carebyzinc/entity_subcategory',
    'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
));

$installer->endSetup();
