<?php
 /**
 * ZincPlatform
 * @package    Zinc_Carebyzinc
 * @copyright  Copyright (c) 2016-2017 Zinplatform (http://www.zincplatform.com/)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */ 
$installer = $this;
$setup = new Mage_Eav_Model_Entity_Setup('core_setup');
$installer->startSetup();
$setup->addAttributeGroup('catalog_product', 'Default', 'Zinc Admin', 1000);
$setup->addAttribute('catalog_product', 'carebyzinc', array(
    'group'             => 'Zinc Admin',
    'label'             => 'Care by Zinc',
    'input'				=> 'boolean',
	'type' 				=> 'int',
    'source'            => 'eav/entity_attribute_source_boolean',
	'default' 			=> '0',
    'visible'           => true,
    'required'          => false,
    'user_defined'      => false,
    'searchable'        => false,
    'filterable'        => false,
    'comparable'        => false,
    'visible_on_front'  => true,
    'visible_in_advanced_search' => false,
    'unique'            => false,
    'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
));


$installer->endSetup();
