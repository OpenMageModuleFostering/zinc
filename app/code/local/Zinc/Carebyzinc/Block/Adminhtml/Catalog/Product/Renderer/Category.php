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
         document.getElementById('carebyzinc_category').onchange = function() {getSubcategories(this.value)};
         
         function getSubcategories(selectElement){
           var reloadurl = '".   Mage::getUrl('adminhtml/product/getSubcategories')."';
           if(selectElement){
      	   new Ajax.Request(reloadurl, {parameters: {  cat: selectElement},
           method: 'post',         
           onComplete: function(transport) {
		var content = JSON.parse(transport.responseText); 
		var i = 0;
		for (var key in content) {
			document.getElementById('carebyzinc_subcategory').options[i] = new Option(content[key],content[key]);
			i++;
		}
					
            }
        });
        }else
        	document.getElementById('carebyzinc_subcategory').innerHTML='';
    }</script>";
                        
    }
}
