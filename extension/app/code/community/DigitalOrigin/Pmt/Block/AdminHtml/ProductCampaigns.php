<?php

/**
 * @method void setArticlesId(string[] $articlesId)
 * @method string[] getArticlesId()
 * @method array[] getArticlesId()
 */
class Aplazame_Aplazame_Block_AdminHtml_ProductCampaigns extends Mage_Adminhtml_Block_Widget
{
    public function __construct()
    {
        parent::__construct();

        $this->setTemplate('aplazame/productCampaigns.phtml');
    }

    public function getArticles()
    {
        $articlesId = $this->getArticlesId();
        $products = Mage::getModel('catalog/product');

        $articles = array();
        foreach ($articlesId as $articleId) {
            /** @var Mage_Catalog_Model_Product $product */
            $product = $products->load($articleId);

            $articles[] = Aplazame_Aplazame_Api_BusinessModel_Article::createFromProduct($product);
        }

        return $articles;
    }
}
