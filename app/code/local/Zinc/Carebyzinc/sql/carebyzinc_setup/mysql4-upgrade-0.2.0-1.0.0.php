<?php
 /**
 * ZincPlatform
 * @package    Zinc_Carebyzinc
 * @copyright  Copyright (c) 2016-2017 Zinplatform (http://www.zincplatform.com/)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */ 
$installer = new Mage_Sales_Model_Resource_Setup('core_setup');
$entities = array(
    'quote_item',
    'order_item'
);
$options = array(
    'type'     => Varien_Db_Ddl_Table::TYPE_TEXT,
    'visible'  => true,
    'required' => false
);
foreach ($entities as $entity) {
    $installer->addAttribute($entity, 'carebyzinc_option', $options);
}
																																											
$installer->endSetup();

