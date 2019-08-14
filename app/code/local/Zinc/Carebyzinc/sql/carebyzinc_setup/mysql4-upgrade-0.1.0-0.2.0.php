<?php
 /**
 * ZincPlatform
 * @package    Zinc_Carebyzinc
 * @copyright  Copyright (c) 2016-2017 Zinplatform (http://www.zincplatform.com/)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */ 
$installer=$this;
$installer->startSetup();

$installer->run("	
DROP TABLE IF EXISTS {$this->getTable('zinc_carebyzinc_order')};
CREATE TABLE {$this->getTable('zinc_carebyzinc_order')} (
  `id` int(11) unsigned NOT NULL auto_increment,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL default '',
  `product_sku` varchar(255) NOT NULL default '',
  `carebyzinc_key` int(11) NOT NULL,
  `carebyzinc_provider` varchar(255) NOT NULL default '',
  `customer_name` varchar(255) NOT NULL default '',
  `customer_email` varchar(255) NOT NULL default '',
  `created_time` datetime NULL,  
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");
																																											
$installer->endSetup();

