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
        $collection->addAttributeToFilter('visibility',array('neq'=>1));
		$collection->addAttributeToSelect('carebyzinc');
        $collection->addAttributeToSelect('carebyzinc_category');
		$collection->addAttributeToSelect('carebyzinc_subcategory');
		$collection->addExpressionAttributeToSelect('carebyzinc','round({{carebyzinc}},0)','carebyzinc');
        
        $select = $collection->getSelect();
        $aliasCategory = 'catalog_category_product';

        $subselect = $collection->getConnection()->select();
        $subselect->from(
            array(
                'main_table' => $collection->getTable('catalog/category_product'),
            ),
            array(
                'product_id',
            )
        );
        $subselect->columns(array(
            'category_ids' => new Zend_Db_Expr("GROUP_CONCAT(`main_table`.`category_id`)")
        ));
        $subselect->group('main_table.product_id');
        
        $select->joinLeft(
            array($aliasCategory => $subselect),
            "`e`.`entity_id` = `{$aliasCategory}`.`product_id`",
            array('category_ids')
        );
        
        parent::setCollection($collection);
	}
  
    protected function _prepareColumns()
    {
        $this->addColumn('entity_id', array(
            'header'=> Mage::helper('catalog')->__('ID'),
            'width' => '30px',
            'type'  => 'number',
            'index' => 'entity_id',
        ));
        
        $this->addColumn('name', array(
            'header'=> Mage::helper('catalog')->__('Name'),
            'index' => 'name',
            'width' => '400px'
        ));

        $store = $this->_getStore();
        if ($store->getId()) {
            $this->addColumn('custom_name', array(
                'header'=> Mage::helper('catalog')->__('Name in %s', $store->getName()),
                'index' => 'custom_name',
            ));
        }
		
        $this->addColumn('sku', array(
            'header'=> Mage::helper('catalog')->__('SKU'),
            'width' => '80px',
            'index' => 'sku',
        ));

        $this->addColumn('price', array(
            'header'=> Mage::helper('catalog')->__('Price'),
            'type'  => 'price',
            'currency_code' => $store->getBaseCurrency()->getCode(),
            'index' => 'price',
        ));
        
        $this->addColumn('category_ids', array(
            'header'=> Mage::helper('catalog')->__('Category'),
            'type' => 'text',
            'width' => '150px',
            'index' => 'category_ids',
            'filter_index' => 'catalog_category_product.category_ids',
            'renderer' => 'Zinc_Carebyzinc_Block_Adminhtml_Catalog_Product_Renderer_List',
            'filter_condition_callback' => array($this, '_categoryFilterCallback')
        ));
        
        $sets = Mage::getResourceModel('eav/entity_attribute_set_collection')
            ->setEntityTypeFilter(Mage::getModel('catalog/product')->getResource()->getTypeId())
            ->load()
            ->toOptionHash();
        $this->addColumnAfter('set_name', array(
            'header'=> Mage::helper('catalog')->__('Attrib. Set Name'),
            'width' => '120px',
            'index' => 'attribute_set_id',
            'type'  => 'options',
            'options' => $sets,
        ), 'name'); 
		
		$this->addColumn('carebyzinc', array(
            'header'=> Mage::helper('catalog')->__('Enable Zinc Quote'),
            'width' => '120px',
            'index' => 'carebyzinc',
            'type'  => 'options',
            'options' => array('1' => 'Enable', '0' => 'Disable')
        ));
        
        $this->addColumn('carebyzinc_category', array(
            'header'=> Mage::helper('catalog')->__('Zinc Category'),
            'index' => 'carebyzinc_category',
            'width' => '120px'
        ));
        
        $this->addColumn('carebyzinc_subcategory', array(
            'header'=> Mage::helper('catalog')->__('Zinc Subcategory'),
            'index' => 'carebyzinc_subcategory',
            'width' => '120px'
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
				 'label'=> Mage::helper('carebyzinc')->__('Update Zinc Enablement'),
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
				 'label'=> Mage::helper('carebyzinc')->__('Update Zinc Category'),
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

    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current'=>true));
    }
    
    protected function _categoryFilterCallback($collection, $column)
    {
        if (!$value = $column->getFilter()->getValue()) {
            return $this;
        }
        
        $categories = Mage::getResourceModel('catalog/category_collection');

        $categories->addAttributeToFilter('name', array('like' => '%' . $value . '%'));

        $select = $categories->getSelect();

        $select->reset(Zend_Db_Select::COLUMNS);
        
        $select->joinLeft(
            array('catalog_category_product' => $collection->getTable('catalog/category_product')),
            'e.entity_id = catalog_category_product.category_id',
            array('product_id')
        );
        
        $collection->addAttributeToFilter($collection->getResource()->getIdFieldName(), array('in' => $select));
        
        return $this;
    }
}
