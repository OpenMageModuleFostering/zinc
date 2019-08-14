<?php
 /**
 * ZincPlatform
 * @package    Zinc_Carebyzinc
 * @copyright  Copyright (c) 2016-2017 Zinplatform (http://www.zincplatform.com/)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */  
 
class Zinc_Carebyzinc_Block_Adminhtml_Catalog_Product_Renderer_Subcategory extends Varien_Data_Form_Element_Select
{
      public function getAfterElementHtml()
    {
        $html = parent::getAfterElementHtml();
       
         return $html."<script>
        
         if(document.getElementById('carebyzinc').value != 1){ 
			$('carebyzinc_subcategory').hide();
			jQuery('label[for=carebyzinc_subcategory], input#carebyzinc_category').hide();

			
		}else{
			$('carebyzinc_subcategory').show();
			jQuery('label[for=carebyzinc_subcategory], input#carebyzinc_category').show();
			document.getElementById('carebyzinc_subcategory').className += ' required-entry';
		}
    
    </script>";
                        
    }
}
