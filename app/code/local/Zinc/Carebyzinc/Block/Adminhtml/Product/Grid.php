<?php
 /**
 * ZincPlatform
 * @package    Zinc_Carebyzinc
 * @copyright  Copyright (c) 2016-2017 Zinplatform (http://www.zincplatform.com/)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */ 
class Zinc_Carebyzinc_Block_Adminhtml_Product_Grid extends Mage_Adminhtml_Block_Catalog_Product_Grid 
{
	public function __construct()
	    {
	        parent::__construct();
	        $this->setId('listGrid');
	        $this->setDefaultSort('entity_id');
	        $this->setDefaultDir('DESC');
	     	$this->setSaveParametersInSession(true); 
	    }
  
	public function setCollection($collection)
	{
	
		//$collection->addAttributeToSelect('carebyzinc');
		$collection->addFieldToFilter('price',array('gteq' => 500));
		$collection->addAttributeToSelect('carebyzinc');
		$collection->addExpressionAttributeToSelect('carebyzinc','round({{carebyzinc}},0)','carebyzinc');
		parent::setCollection($collection);
		
	}
  
   protected function _prepareColumns()
    {
        $this->addColumn('entity_id',
            array(
                'header'=> Mage::helper('catalog')->__('ID'),
                'width' => '50px',
                'type'  => 'number',
                'index' => 'entity_id',
        ));
        $this->addColumn('name',
            array(
                'header'=> Mage::helper('catalog')->__('Name'),
                'index' => 'name',
        ));

        $store = $this->_getStore();
        if ($store->getId()) {
            $this->addColumn('custom_name',
                array(
                    'header'=> Mage::helper('catalog')->__('Name in %s', $store->getName()),
                    'index' => 'custom_name',
            ));
        }
		
        $this->addColumn('sku',
            array(
                'header'=> Mage::helper('catalog')->__('SKU'),
                'width' => '80px',
                'index' => 'sku',
        ));

        $store = $this->_getStore();
        $this->addColumn('price',
            array(
                'header'=> Mage::helper('catalog')->__('Price'),
                'type'  => 'price',
                'currency_code' => $store->getBaseCurrency()->getCode(),
                'index' => 'price',
        ));
		
		 $this->addColumn('carebyzinc',
            array(
                'header'=> Mage::helper('catalog')->__('Care by Zinc'),
                'width' => '150px',
                'index' => 'carebyzinc',
                'type'  => 'options',
		'options' => array('1' => 'Enable', '0' => 'Disable')
        ));
        $this->addColumn('action', array(
                'header'    =>  Mage::helper('carebyzinc')->__('Action'),
                'width'     => '100',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => array(
                    array(
                        'caption'   => Mage::helper('carebyzinc')->__('Edit'),
                        'url'       => array('base'=> '*/*/edit'),
						'title'     => 'Edit',
                        'field'     => 'id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                'is_system' => true,
        ));
        return $this;
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('product');
		
	$carebyzinc = Mage::getSingleton('carebyzinc/carebyzinc')->getOptionArray();

        $this->getMassactionBlock()->addItem('zinc_carebyzinc', array(
				 'label'=> Mage::helper('carebyzinc')->__('Change Care by Zinc'),
				 'url'  => $this->getUrl('adminhtml/product/massCarebyzinc', array('_current'=>true,'pid'=>1)),
				 'additional' => array(
				 'visibility' => array(
				 'name' => 'zinc_carebyzinc',
				 'type' => 'select',
				 'class' => 'required-entry',
				 'values' => $carebyzinc
			   )
             )
        ));
         $this->getMassactionBlock()->addItem('carebyzinc_category', array(
				 'label'=> Mage::helper('carebyzinc')->__('Change Category'),
				 'url'  => $this->getUrl('adminhtml/product/massCategory', array('_current'=>true,'pid'=>1)),
				 'additional' => array(
				 'visibility' => array(
				 'name' => 'carebyzinc_category',
				 'type' => 'select',
				 'class' => 'required-entry',
				 'values' => Mage::getModel('carebyzinc/carebyzinc')->getCategoryArray()
			   )
             )
        ));
        
	
      
        return $this;
    }
  
  public function getRowUrl($row)
  {
      return $this->getUrl('*/*/edit', array('id'=>$row->getId()));
  }
  public function getGridUrl(){
    return $this->getUrl('*/*/grid', array('_current'=>true));
  }

}
