<?php
 /**
 * ZincPlatform
 * @package    Zinc_Carebyzinc
 * @copyright  Copyright (c) 2016-2017 Zinplatform (http://www.zincplatform.com/)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */  
 
class Zinc_Carebyzinc_Block_Adminhtml_Catalog_Product_Renderer_Model extends Varien_Data_Form_Element_Text
{
      public function getAfterElementHtml()
    {
        $html = parent::getAfterElementHtml();
       
         return $html."<script>
        
        if((document.getElementById('carebyzinc').value == 1) && (subarray.indexOf(category) >= 0)){ 						
			var category = document.getElementById('carebyzinc_category').value.toLowerCase();
			var subarray = ['bicycle','electronics'];				
				$('carebyzinc_model').show();
				jQuery('label[for=carebyzinc_model], input#carebyzinc_model').show();
				document.getElementById('carebyzinc_model').className += ' required-entry validate-length maximum-length-30 minimum-length-1';
			
		}else{
			$('carebyzinc_model').hide();
			jQuery('label[for=carebyzinc_model], input#carebyzinc_model').hide();
		}
    
    </script>";
                        
    }
}
