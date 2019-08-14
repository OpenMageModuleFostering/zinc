<?php
 /**
 * ZincPlatform
 * @package    Zinc_Carebyzinc
 * @copyright  Copyright (c) 2016-2017 Zinplatform (http://www.zincplatform.com/)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */ 
class Zinc_Carebyzinc_Block_Adminhtml_Catalog_Product_Grid extends Mage_Adminhtml_Block_Catalog_Product_Grid
{
  
 public function setCollection($collection)
	{
		$collection->addExpressionAttributeToSelect('carebyzinc','round({{carebyzinc}},0)','carebyzinc');		
		parent::setCollection($collection);
	}
	
    protected function _prepareColumns()
    {
        
//	$this->addColumnAfter('carebyzinc',
//            array(
//                'header'=> Mage::helper('catalog')->__('Care by Zinc'),
//                'width' => '70px',
//                'index' => 'carebyzinc',
//                'type'  => 'options',
//                'options' => array('1' => 'Enabled', '0' => 'Disabled'),
//        ), 'visibility');

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('product');

        $this->getMassactionBlock()->addItem('delete', array(
             'label'=> Mage::helper('catalog')->__('Delete'),
             'url'  => $this->getUrl('*/*/massDelete'),
             'confirm' => Mage::helper('catalog')->__('Are you sure?')
        ));

        $statuses = Mage::getSingleton('catalog/product_status')->getOptionArray();

        array_unshift($statuses, array('label'=>'', 'value'=>''));
        $this->getMassactionBlock()->addItem('status', array(
             'label'=> Mage::helper('catalog')->__('Change status'),
             'url'  => $this->getUrl('*/*/massStatus', array('_current'=>true)),
             'additional' => array(
                    'visibility' => array(
                         'name' => 'status',
                         'type' => 'select',
                         'class' => 'required-entry',
                         'label' => Mage::helper('catalog')->__('Status'),
                         'values' => $statuses
                     )
             )
        ));
		
	$carebyzinc = Mage::getSingleton('carebyzinc/carebyzinc')->getOptionArray();

//        $this->getMassactionBlock()->addItem('zinc_carebyzinc', array(
//				 'label'=> Mage::helper('carebyzinc')->__('Change Carebyzinc'),
//				 'url'  => $this->getUrl('adminhtml/product/massCarebyzinc', array('_current'=>true)),
//				 'additional' => array(
//				 'visibility' => array(
//				 'name' => 'zinc_carebyzinc',
//				 'type' => 'select',
//				 'class' => 'required-entry',
//				 'label' => Mage::helper('carebyzinc')->__('Care by Zinc'),
//				 'values' => $carebyzinc
//			   )
//             )
//        ));

        if (Mage::getSingleton('admin/session')->isAllowed('catalog/update_attributes')){
            $this->getMassactionBlock()->addItem('attributes', array(
                'label' => Mage::helper('catalog')->__('Update Attributes'),
                'url'   => $this->getUrl('*/catalog_product_action_attribute/edit', array('_current'=>true))
            ));
        }

        Mage::dispatchEvent('adminhtml_catalog_product_grid_prepare_massaction', array('block' => $this));
        return $this;
    }
  
}
