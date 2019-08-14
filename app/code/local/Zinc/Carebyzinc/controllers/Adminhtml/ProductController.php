<?php
 /**
 * ZincPlatform
 * @package    Zinc_Carebyzinc
 * @copyright  Copyright (c) 2016-2017 Zinplatform (http://www.zincplatform.com/)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */ 
class Zinc_Carebyzinc_Adminhtml_ProductController extends Mage_Adminhtml_Controller_action
{

	protected function _initAction() {
		$this->loadLayout()
			->_setActiveMenu('carebyzinc/product')
			->_addBreadcrumb(Mage::helper('adminhtml')->__('Care by Zinc'), Mage::helper('adminhtml')->__('Care by Zinc'));
		
		return $this;
	}   
 
	public function indexAction() {
		$this->_initAction()
			->renderLayout();
	}
	
	
	public function orderAction() {
		$this->_initAction()
			->renderLayout();
	}
	public function gridAction()
 	{	   
	     $this->loadLayout();
       	     $this->renderLayout();
 	}
 	/**
     * Edit Carebyzinc status action
     *
     */
 	public function editAction() {
		$id     = $this->getRequest()->getParam('id');
		$model  = Mage::getModel('catalog/product')->load($id);
		if ($model->getId() || $id == 0) {
			$data = Mage::getSingleton('adminhtml/session')->getFormData(true);
			if (!empty($data)) {
				$model->setData($data);
			}

			Mage::register('carebyzinc_data', $model);
			$this->loadLayout();
			$this->_setActiveMenu('carebyzinc/products');
			$this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
			$this->_addContent($this->getLayout()->createBlock('carebyzinc/adminhtml_product_edit'))
				->_addLeft($this->getLayout()->createBlock('carebyzinc/adminhtml_product_edit_tabs'));
			$this->renderLayout();
		} else {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('carebyzinc')->__('Item does not exist'));
			$this->_redirect('*/*/');
		}
	}
     /**
     * Update product(s) status action
     *
     */
	public function saveAction()
    	{
    		$post = $this->getRequest()->getPost();	
    		$id     = $this->getRequest()->getParam('id');
		$model  = Mage::getModel('catalog/product')->load($id);
		$model->setCarebyzincCategory($post['category']);
		$model->setCarebyzincSubcategory($post['subcategory']);
		$model->setCarebyzinc($post['carebyzinc']);
		$model->save();
		$this->_redirect('*/*/');
		return;
    	
    	}
	
    /**
     * Update product(s) status action
     *
     */
    public function massCarebyzincAction()
    {
        $productIds = (array)$this->getRequest()->getParam('product');
        $carebyzinc     = (int)$this->getRequest()->getParam('zinc_carebyzinc');

        try {
            $this->_validateMassCarebyzinc($productIds, $carebyzinc);
            Mage::getSingleton('catalog/product_action')
                ->updateAttributes($productIds, array('carebyzinc' => $carebyzinc));
				
            $this->_getSession()->addSuccess(
                $this->__('Total of %d record(s) have been updated.', count($productIds))
            );
        }
        catch (Mage_Core_Model_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (Exception $e) {
            $this->_getSession()
                ->addException($e, $this->__('An error occurred while updating the product(s)'));
        }
	if($this->getRequest()->getParam('pid'))
      		 $this->_redirect('*/*/');
      	else
      		$this->_redirect('adminhtml/catalog_product/index');
    }
    
     /**
     * Update product(s) category action
     *
     */
    public function massCategoryAction()
    {
        $productIds = (array)$this->getRequest()->getParam('product');
        $category     = $this->getRequest()->getParam('carebyzinc_category');
        $subCat = 'Other';
	if(!is_array($productIds)) {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('carebyzinc')->__('Please select Product(s).'));
	}else {
	        try {
	            $this->_validateMassCarebyzinc($productIds, $category);
	            $subCategories = Mage::getModel('carebyzinc/carebyzinc')->getSubCategoryArray($category);
	            foreach ($productIds as $product_id) {
			$productModel = Mage::getModel('catalog/product')->load($product_id);
			$name = $productModel->getName();			
			foreach($subCategories as $subcat){
				if (stripos($name, $subcat) !== false) {
				     $subCat = $subcat;
				     break;
				}
			}			
			$productModel->setCarebyzincCategory($category);
			$productModel->setCarebyzincSubcategory($subCat);
			$productModel->save();
					
		 }
					
	            $this->_getSession()->addSuccess(
	                $this->__('Total of %d record(s) have been updated.', count($productIds))
	            );
	        }
	        catch (Mage_Core_Model_Exception $e) {
	            $this->_getSession()->addError($e->getMessage());
	        } catch (Mage_Core_Exception $e) {
	            $this->_getSession()->addError($e->getMessage());
	        } catch (Exception $e) {
	            $this->_getSession()
	                ->addException($e, $this->__('An error occurred while updating the product(s)'));
	        }
        }
	if($this->getRequest()->getParam('pid'))
      		 $this->_redirect('*/*/');
      	else
      		$this->_redirect('adminhtml/catalog_product/index');
    }       
	
   public function _validateMassCarebyzinc(array $productIds, $carebyzinc)
    {
       if (!Mage::getModel('catalog/product')->isProductsHasSku($productIds)) {
                throw new Mage_Core_Exception(
                    $this->__('Some of the processed products have no SKU value defined. Please fill it prior to performing operations on these products.')
                );
      }
    }
    
    public function getSubcategoriesAction()
    {
	$cid = $this->getRequest()->getParam('cat');
	$subCategories = Mage::getModel('carebyzinc/carebyzinc')->getSubCategoryArray($cid);		
	return $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($subCategories));
      
    }
}
