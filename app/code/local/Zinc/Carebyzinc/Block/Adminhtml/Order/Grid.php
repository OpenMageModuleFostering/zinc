<?php
 /**
 * ZincPlatform
 * @package    Zinc_Carebyzinc
 * @copyright  Copyright (c) 2016-2017 Zinplatform (http://www.zincplatform.com/)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */ 
class Zinc_Carebyzinc_Block_Adminhtml_Order_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
	
 public function __construct()
  {
      parent::__construct();
      $this->setId('orderGrid');
      $this->setDefaultSort('id');
      $this->setDefaultDir('DESC');
      $this->setSaveParametersInSession(true);
  }

  protected function _prepareCollection()
  {
      $collection = Mage::getModel('carebyzinc/order')->getCollection();
      $this->setCollection($collection);
      return parent::_prepareCollection();
  }

  protected function _prepareColumns()
  {
      $this->addColumn('id', array(
          'header'    => Mage::helper('carebyzinc')->__('ID'),
          'align'     =>'right',
          'width'     => '50px',
          'index'     => 'id',
      ));
	  
	   $this->addColumn('order_id', array(
          'header'    => Mage::helper('carebyzinc')->__('Order ID'),
          'align'     =>'right',
          'width'     => '50px',
          'index'     => 'order_inc_id',
      ));
	  
	  $this->addColumn('product_id', array(
          'header'    => Mage::helper('carebyzinc')->__('Product ID'),
          'align'     =>'right',
          'width'     => '50px',
          'index'     => 'product_id',
      ));
		
	$this->addColumn('product_name', array(
          'header'    => Mage::helper('carebyzinc')->__('Product'),
          'align'     =>'left',
          'index'     => 'product_name',
      ));
      $this->addColumn('price', array(
          'header'    => Mage::helper('carebyzinc')->__('Price'),
          'align'     =>'left',
          'index'     => 'product_price',
      ));
      $this->addColumn('warrenty_price', array(
          'header'    => Mage::helper('carebyzinc')->__('Warrenty Price'),
          'align'     =>'left',
          'index'     => 'warrenty_price',
      ));
	  
	 $this->addColumn('customer_name', array(
          'header'    => Mage::helper('carebyzinc')->__('Customer Name'),
          'align'     =>'left',
          'index'     => 'customer_name',
      ));
	  
      $this->addColumn('customer_email', array(
          'header'    => Mage::helper('carebyzinc')->__('Email'),
          'align'     =>'left',
          'index'     => 'customer_email',
      ));

	   $this->addColumn('carebyzinc_key', array(
          'header'    => Mage::helper('carebyzinc')->__('Care by Zinc Key'),
          'align'     =>'right',
          'index'     => 'carebyzinc_key',
      ));
	  
	  $this->addColumn('carebyzinc_provider', array(
          'header'    => Mage::helper('carebyzinc')->__('Provider'),
          'align'     =>'left',
          'index'     => 'carebyzinc_provider',
      ));
		
      return parent::_prepareColumns();
  }
   
 public function getRowUrl($row)
    {
        if (Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/view')) {
            return $this->getUrl('*/sales_order/view', array('order_id' => $row->getOrderId()));
        }
        return false;
    }


}
