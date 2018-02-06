<?php

/**
 * Class DigitalOrigin_Pmt_Model_Observer
 */
class DigitalOrigin_Pmt_Model_Observer
{
    const PROMOTIONS_CATEGORY = 'paylater-promotion-product';

    const AUTOLOADER_FILE = '/DigitalOrigin/autoload.php';

    /**
     * @return $this
     */
    public function addAutoloader()
    {
        $baseDir = Mage::getBaseDir('lib');
        $autoloadPath = self::AUTOLOADER_FILE;
        $autoload = $baseDir.$autoloadPath;

        require_once $autoload;

        return $this;
    }

    /**
     * @return $this
     */
    public function addCategory()
    {
        return true;
        $promotionCategory = Mage::getResourceModel('catalog/category_collection')
            ->addFieldToFilter('name', self::PROMOTIONS_CATEGORY)->getFirstItem();
        if (!$promotionCategory instanceof Mage_Catalog_Model_Category || !$promotionCategory->getEntityId()) {
            try {
                /** @var Mage_Catalog_Model_Category $category */
                $category = Mage::getModel('catalog/category');
                $category->setName(self::PROMOTIONS_CATEGORY);
                $category->setUrlKey('paylater');
                $category->setIsActive(0);
                $category->setDisplayMode(Mage_Catalog_Model_Category::DM_PRODUCT);
                $category->setIsAnchor(0);
                $category->setDescription(Mage::helper('pmt')->__(
                    'Paga+Tarde: Los productos con esta categoría tienen financiación gratis asumida por el comercio.
                     Úsalo para promocionar tus productos o marcas.'
                ));
                $category->setStoreId(Mage::app()->getStore()->getId());
                $parentId = Mage_Catalog_Model_Category::TREE_ROOT_ID;
                $parentCategory = Mage::getModel('catalog/category')->load($parentId);
                $category->setPath($parentCategory->getPath());
                $category->save();
            } catch (\Exception $e) {
                return false;
            }
        }

        return $this;
    }
}
