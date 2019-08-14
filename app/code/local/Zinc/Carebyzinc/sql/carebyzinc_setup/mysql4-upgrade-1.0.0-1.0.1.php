<?php
 /**
 * ZincPlatform
 * @package    Zinc_Carebyzinc
 * @copyright  Copyright (c) 2016-2017 Zinplatform (http://www.zincplatform.com/)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */ 
$installer = $this;
$installer->startSetup();

$this->startSetup();

$this->getConnection()
   ->addColumn($installer->getTable('zinc_carebyzinc_order'),'order_inc_id',"varchar(200) COMMENT 'OrderIncrementId'");
	
$this->getConnection()
    ->addColumn($installer->getTable('zinc_carebyzinc_order'),'item_id',"INT(11) COMMENT 'ItemId'");
 
$this->getConnection()
   ->addColumn($installer->getTable('zinc_carebyzinc_order'),'warrenty_price',"double COMMENT 'WarrentyPrice'");
$this->getConnection()
   ->addColumn($installer->getTable('zinc_carebyzinc_order'),'product_price',"double COMMENT 'ProductPrice'");
