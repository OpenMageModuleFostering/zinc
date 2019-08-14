<?php
 /**
 * ZincPlatform
 * @package    Zinc_Carebyzinc
 * @copyright  Copyright (c) 2016-2017 Zinplatform (http://www.zincplatform.com/)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */  
 
class Zinc_Carebyzinc_Block_Adminhtml_Catalog_Product_Renderer_Manufacturer extends Varien_Data_Form_Element_Text
{
      public function getAfterElementHtml()
    {
        $html = parent::getAfterElementHtml();
       
        return $html."<script>
        var category = document.getElementById('carebyzinc_category').value.toLowerCase();
		var subarray = ['bicycle','electronics'];	
        if((document.getElementById('carebyzinc').value == 1) && (subarray.indexOf(category) >= 0)){ 						
				$('carebyzinc_manufacturer').show();
				jQuery('label[for=carebyzinc_manufacturer], input#carebyzinc_manufacturer').show();	
				document.getElementById('carebyzinc_manufacturer').className += ' required-entry validate-length maximum-length-30 minimum-length-1';
				
		}else{
			$('carebyzinc_manufacturer').hide();
			jQuery('label[for=carebyzinc_manufacturer], input#carebyzinc_manufacturer').hide();			
		}
    
    </script>";
                        
    }
}
