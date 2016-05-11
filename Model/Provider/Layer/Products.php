<?php

/*
 * This file is part of Glugox.
 *
 * (c) Glugox <glugox@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Glugox\PDF\Model\Provider\Layer;

/**
 * Description of Products
 *
 * @author Glugox
 */
class Products extends \Glugox\PDF\Model\Provider\Products implements \Glugox\PDF\Model\Provider\Products\ProviderInterface {

    /**
     * @var \Magento\Catalog\Block\Product\ListProduct
     */
    protected $_block;

    /**
     * @var \Magento\Framework\View\LayoutInterface
     */
    protected $_layout;


    protected $_filtersUsed;

    /**
     * @return mixed
     */
    public function getFiltersUsed()
    {
        return $this->_filtersUsed;
    }


    /**
     * @var \Magento\LayeredNavigation\Block\Navigation
     */
    //protected $_navigation;

    /**
     * Returns products by categories
     *
     * NOTE: This should be never called on any frontend pages specially
     * on layered (anchor) category pages, as it would affect the layered navigation.
     * It is intented to be called only on /pdf/category/print like pages where the pages serves the
     * pdf type response.
     *
     * @param array $categories
     * @return \Magento\Framework\Api\ExtensibleDataInterface
     */
    public function getProductsByAnchorCategory(\Magento\Catalog\Model\Category $category, \Glugox\PDF\Model\PDFResult $pdfResult = null) {

        $this->_helper->setRegisteredLayerCategory($category);
        $this->_layerResolver->get(\Magento\Catalog\Model\Layer\Resolver::CATALOG_LAYER_CATEGORY);

        $this->_layout = $this->_helper->createInstance("Magento\Framework\View\LayoutInterface");

        
        $this->_navigation = $this->_layout->createBlock("Magento\LayeredNavigation\Block\Navigation\Category");
        $this->_block = $this->_layout->createBlock("Magento\Catalog\Block\Product\ListProduct");
        $toolbar = $this->_block->getToolbarBlock();

        $layer = $this->_block->getLayer();
        $collection = $layer->getProductCollection();
        $this->_block->prepareSortableFieldsByCategory($category);

        // set collection to toolbar and apply sort
        $toolbar->setCollection($collection);

        // reset limit from toolbar
        $collection->setPageSize($this->_helper->getConfigObject()->getMaxNumberOfProducts());

        $this->_filtersUsed = [];
        $filters = $this->_navigation->getFilters();

        /** @var \Magento\Catalog\Model\Layer\Filter\AbstractFilter $filter */
        foreach ($filters as $filter) {
            $filter->apply($this->_helper->getRequest());
            $reqVar = $filter->getRequestVar();
            if($reqVal = $this->_helper->getRequest()->getParam($reqVar)){
                $this->_filtersUsed[$reqVar] = $reqVal;
            }
        }

        if($pdfResult && \count($this->_filtersUsed)){
            $pdfResult->setFiltersUsed($this->_filtersUsed);
        }

        $layer->apply();

        return $collection;
    }


    /**
     * Returns products by categories
     *
     * @param array $categories
     * @return \Magento\Framework\Api\ExtensibleDataInterface
     */
    public function getProductsByCategories(array $categories, \Glugox\PDF\Model\PDFResult $pdfResult = null, $onlyCount=false) {

        if (\count($categories) > 1) {
            throw new \Glugox\PDF\Exception\PDFException(__("Multiple categories not implemented yet!"));
        }

        $category = \reset($categories);
        if (!$category instanceof \Magento\Catalog\Api\Data\CategoryInterface) {
            $category = $this->categoryRepository->get($category, $this->_helper->getCurrentStoreId());
        }
        if (!$category->getIsAnchor()) {
            //throw new \Glugox\PDF\Exception\PDFException(__("Non anchor categories not implemented yet!"));
            return parent::getProductsByCategories($categories, $pdfResult);
        } else {
            return $this->getProductsByAnchorCategory($category, $pdfResult);
        }
    }


    /**
     * Returns products by skus
     *
     * @param array $skus
     */
    public function getProductsBySkus(array $skus) {
        return parent::getProductsBySkus($skus);
    }


    /**
     * Returns product by sku
     *
     * @param type $sku
     * @return type
     */
    public function getProductBySku($sku) {
        return parent::getProductBySku($sku);
    }


    /**
     * Returns product by id
     *
     * @param type $id
     * @return type
     */
    public function getProductById($id) {
        return parent::getProductById($id);
    }


    /**
     * Returns all products
     */
    public function getAllProducts() {
        return parent::getAllProducts();
    }


    /**
     * Returns number of products to be executed by categories
     *
     * @param array $categories
     * @return \Magento\Framework\Api\ExtensibleDataInterface
     */
    public function getProductCountByCategories(array $categories) {
        if (\count($categories) > 1) {
            throw new \Glugox\PDF\Exception\PDFException(__("Multiple categories not supported!"));
        }
        $category = \reset($categories);
        if (!$category instanceof \Magento\Catalog\Api\Data\CategoryInterface) {
            $category = $this->categoryRepository->get($category, $this->_helper->getCurrentStoreId());
        }

        if (!$category->getIsAnchor()) {
            return parent::getProductCountByCategories($categories);
        } else {
            return $this->getProductCountByAnchorCategory($category);
        }
    }


    /**
     * Returns number of products to be executed by anchor category
     *
     * @param \Magento\Catalog\Model\Category $category
     * @return int
     */
    public function getProductCountByAnchorCategory(\Magento\Catalog\Model\Category $category) {
        return $this->_layerResolver->get(\Magento\Catalog\Model\Layer\Resolver::CATALOG_LAYER_CATEGORY)
                ->getProductCollection()
                ->getSize();
    }


}
