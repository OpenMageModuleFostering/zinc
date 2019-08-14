<?php
 /**
 * ZincPlatform
 * @package    Zinc_Carebyzinc
 * @copyright  Copyright (c) 2016-2017 Zinplatform (http://www.zincplatform.com/)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */ 
class Zinc_Carebyzinc_Model_Sales_Quote extends Mage_Sales_Model_Quote
{    
    /**
     * Retrieve quote item by product id
     *
     * @param   Mage_Catalog_Model_Product $product
     * @return  Mage_Sales_Model_Quote_Item || false
     */
    public function getItemByProduct($product)
    {
		$product = Mage::getModel('catalog/product')->load($product); 
		$productType = $product->getTypeId();
        if(($productType == Mage_Catalog_Model_Product_Type::TYPE_SIMPLE || $productType == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE) && ($product->getCarebyzinc() == 1))
			 return false;
		else{
			foreach ($this->getAllItems() as $item) {
				if ($item->representProduct($product)) {
					return $item;
				}
			}
		}
        return false;
    }   
}
