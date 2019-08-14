<?php
/**
 * ZincPlatform
 * @package    Zinc_Carebyzinc
 * @copyright  Copyright (c) 2016-2017 Zinplatform (http://www.zincplatform.com/)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Zinc_Carebyzinc_Block_Carebyzinc extends Mage_Core_Block_Template
{
    public function _prepareLayout()
    {
        return parent::_prepareLayout();
    }

    public function getCarebyzinc()
    {
        if (!$this->hasData('carebyzinc')) {
            $this->setData('carebyzinc', Mage::registry('carebyzinc'));
        }
        return $this->getData('carebyzinc');
    }
}
