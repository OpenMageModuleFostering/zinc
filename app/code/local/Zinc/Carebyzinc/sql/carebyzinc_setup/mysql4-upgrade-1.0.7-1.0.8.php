<?php
 /**
 * ZincPlatform
 * @package    Zinc_Carebyzinc
 * @copyright  Copyright (c) 2016-2017 Zinplatform (http://www.zincplatform.com/)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */ 
 
$installer = $this;

$installer->startSetup();

$sku = 'Care by Zinc';
$product = Mage::getModel('catalog/product')->loadByAttribute('sku', $sku);
$productId = $product->getId();

Mage::getConfig()->saveConfig('carebyzinc/general/warranty_product', $product->getId(), 'default', 0);
          																						
$installer->endSetup();
