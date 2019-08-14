<?php
 /**
 * ZincPlatform
 * @package    Zinc_Carebyzinc
 * @copyright  Copyright (c) 2016-2017 Zinplatform (http://www.zincplatform.com/)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */  
 
class Zinc_Carebyzinc_Block_Adminhtml_Catalog_Product_Renderer_Category extends Varien_Data_Form_Element_Select
{
      public function getAfterElementHtml()
    {
        $html = parent::getAfterElementHtml();
       
         return $html."<script>
        
        if(document.getElementById('carebyzinc').value != 1){ 
			$('carebyzinc_category').hide();
			jQuery('label[for=carebyzinc_category], input#carebyzinc_category').hide();

		}else{
			$('carebyzinc_category').show();
			document.getElementById('carebyzinc_category').className += ' required-entry';
		}
         document.getElementById('carebyzinc_category').onchange = function() {
			getSubcategories(this.value)
			var category = this.value.toLowerCase();
			var subarray = ['bicycle','electronics'];				
			if(subarray.indexOf(category) >= 0){
				$('carebyzinc_manufacturer').show();
				jQuery('label[for=carebyzinc_manufacturer], input#carebyzinc_manufacturer').show();	
				$('carebyzinc_model').show();
				jQuery('label[for=carebyzinc_model], input#carebyzinc_model').show();	
				document.getElementById('carebyzinc_manufacturer').className += ' required-entry validate-length maximum-length-30 minimum-length-1';
				document.getElementById('carebyzinc_model').className += ' required-entry validate-length maximum-length-30 minimum-length-1';
			}else{
				$('carebyzinc_manufacturer').hide();
				jQuery('label[for=carebyzinc_manufacturer], input#carebyzinc_manufacturer').hide();	
				$('carebyzinc_model').hide();
				jQuery('label[for=carebyzinc_model], input#carebyzinc_model').hide();		
			
			}
         
         
         };
         
         function getSubcategories(selectElement){
           var reloadurl = '".   Mage::getUrl('adminhtml/product/getSubcategories')."';
           if(selectElement){
      	   new Ajax.Request(reloadurl, {parameters: {  cat: selectElement},
           method: 'post',         
           onComplete: function(transport) {
				var content = JSON.parse(transport.responseText); 
				var i = 0;
				document.getElementById('carebyzinc_subcategory').options.length = 0;
				for (var key in content) {
					document.getElementById('carebyzinc_subcategory').options[i] = new Option(content[key],key);
					i++;
				}
					
            }
        });
        }else
        	document.getElementById('carebyzinc_subcategory').innerHTML='';
    }
    document.getElementById('carebyzinc').onchange = function() {
		if(this.value == 1){
			$('carebyzinc_category').show();
			$('carebyzinc_subcategory').show();
			jQuery('label[for=carebyzinc_category], input#carebyzinc_category').show();
			jQuery('label[for=carebyzinc_subcategory], input#carebyzinc_subcategory').show();
			document.getElementById('carebyzinc_category').className += ' required-entry';
			document.getElementById('carebyzinc_subcategory').className += ' required-entry';
		
		}else{
			$('carebyzinc_category').hide();
			$('carebyzinc_subcategory').hide();
			jQuery('label[for=carebyzinc_category], input#carebyzinc_category').hide();
			jQuery('label[for=carebyzinc_subcategory], input#carebyzinc_subcategory').hide();
		}
    };
    
    
    </script>";
                        
    }
}
