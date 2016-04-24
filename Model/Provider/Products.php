<?php

/*
 * This file is part of Glugox.
 *
 * (c) Glugox <glugox@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Glugox\PDF\Model\Provider;

use \Magento\Catalog\Model\Product\Visibility;
use \Magento\Catalog\Api\Data\ProductInterface;
use \Magento\Catalog\Model\Product\Attribute\Source\Status;

/**
 * Description of Products
 *
 * @author Glugox
 */
class Products implements \Glugox\PDF\Model\Provider\Products\ProviderInterface {

    /**
     * @var array|null
     */
    protected $_errors = null;

    /**
     *
     * @return null|string
     */
    public function getError() {
        if($this->_errors === null){
            return null;
        }
        return \count($this->_errors) > 0 ? \implode("; ", $this->_errors) : null;
    }

    /**
     * @param string $msg
     * @return \Glugox\PDF\Model\Provider\Products
     */
    public function addError($msg) {

        $this->_errors = null === $this->_errors ? array() : $this->_errors;
        $this->_errors[] = $msg;
        return $this;
    }


    /**
     *
     * @var Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var \Magento\Catalog\Api\CategoryRepositoryInterface
     */
    protected $categoryRepository;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var \Magento\Framework\Api\FilterBuilder
     */
    protected $filterBuilder;

    /**
     * @var \Magento\Framework\Api\Search\FilterGroupBuilder
     */
    protected $filterGroupBuilder;

    /** @var \Glugox\PDF\Helper\Data */
    protected $_helper;

    /**
     * Catalog Layer Resolver
     *
     * @var \Magento\Catalog\Model\Layer\Resolver
     */
    protected $_layerResolver;

    /**
     * @var \Magento\Catalog\Model\Product\Visibility
     */
    protected $visibility;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $collectionFactory;


    /**
     *
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Framework\Api\FilterBuilder $filterBuilder
     * @param \Magento\Framework\Api\Search\FilterGroupBuilder $filterGroupBuilder
     * @param \Magento\Catalog\Model\Layer\Resolver $layerResolver
     * @param Visibility $visibility
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory
     */
    public function __construct(
    \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
            \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository,
            \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
            \Magento\Framework\Api\FilterBuilder $filterBuilder,
            \Magento\Framework\Api\Search\FilterGroupBuilder $filterGroupBuilder,
            \Magento\Catalog\Model\Layer\Resolver $layerResolver,
            \Magento\Catalog\Model\Product\Visibility $visibility,
            \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory
    ) {
        $this->productRepository = $productRepository;
        $this->categoryRepository = $categoryRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder = $filterBuilder;
        $this->filterGroupBuilder = $filterGroupBuilder;
        $this->_layerResolver = $layerResolver;
        $this->visibility = $visibility;
        $this->collectionFactory = $collectionFactory;
    }


    /**
     *
     * @param \Glugox\PDF\Helper\Data $helper
     * @return \Glugox\PDF\Model\Provider\Products
     */
    public function setHelper(\Glugox\PDF\Helper\Data $helper) {
        $this->_helper = $helper;
        return $this;
    }


    /**
     * Returns products by categories
     *
     * @param array $categories
     * @return \Magento\Framework\Api\ExtensibleDataInterface|int
     */
    public function getProductsByCategories(array $categories,
            $onlyCount = false) {

        foreach ($categories as $category) {
            if (!$category instanceof \Magento\Catalog\Api\Data\CategoryInterface) {
                $category = $this->categoryRepository->get($category, $this->_helper->getCurrentStoreId());
            }
            if (!$category) {
                $this->addError(__("Category %d does not exist!", $category));
                continue;
            }

            $childCategories = $category->getResource()->getAllChildren($category);
            $filter = $this->filterBuilder->setField('category_id')
                    ->setValue($childCategories)
                    ->setConditionType('eq')
                    ->create();

            $this->filterGroupBuilder->addFilter($filter);
        }

        $filter = $this->filterBuilder->setField(ProductInterface::STATUS)->setValue(Status::STATUS_ENABLED)->setConditionType('eq')->create();
        $this->filterGroupBuilder->addFilter($filter);
        $filter = $this->filterBuilder->setField(ProductInterface::VISIBILITY)->setValue(array(Visibility::VISIBILITY_IN_SEARCH, Visibility::VISIBILITY_BOTH))->setConditionType('in')->create();
        $this->filterGroupBuilder->addFilter($filter);

        $filter_group = $this->filterGroupBuilder->create();
        $criteria = $this->searchCriteriaBuilder
                ->setFilterGroups([$filter_group])
                ->setPageSize($this->_helper->getConfigObject()->getMaxNumberOfProducts())
                ->create();

        $list = $this->productRepository->getList($criteria);

        if ($onlyCount) {
            return $list->getTotalCount();
        }

        return $list->getItems();
    }


    /**
     * Returns products by skus
     *
     * @param array $skus
     */
    public function getProductsBySkus(array $skus) {

        foreach ($skus as $sku) {
            $filter = $this->filterBuilder->setField('sku')
                    ->setValue($sku)
                    ->setConditionType('eq')
                    ->create();

            $this->filterGroupBuilder->addFilter($filter);
        }
        $filter_group = $this->filterGroupBuilder->create();
        $criteria = $this->searchCriteriaBuilder
                ->setFilterGroups([$filter_group])
                ->setPageSize($this->_helper->getConfigObject()->getMaxNumberOfProducts())
                ->create();

        $list = $this->productRepository->getList($criteria);
        return $list->getItems();
    }


    /**
     * Returns product by sku
     *
     * @param type $sku
     * @return type
     */
    public function getProductBySku($sku) {

        $product = null;
        try {
            $product = $this->productRepository->get($sku);
        } catch (\Magento\Framework\Exception\NoSuchEntityException $ex) {
            $this->_error = $ex->getMessage();
        }

        return $product;
    }


    /**
     * Returns product by id
     *
     * @param type $id
     * @return type
     */
    public function getProductById($id) {

        $product = null;
        try {
            $product = $this->productRepository->getById($id);
        } catch (\Magento\Framework\Exception\NoSuchEntityException $ex) {
            $this->_error = $ex->getMessage();
        }

        return $product;
    }


    /**
     * Returns all products
     */
    public function getAllProducts() {
        $criteria = $this->searchCriteriaBuilder
                ->setPageSize($this->_helper->getConfigObject()->getMaxNumberOfProducts())
                ->create();

        $list = $this->productRepository->getList($criteria);
        return $list->getItems();
    }


    /**
     * Returns number of products to be executed by categories
     *
     * @param array $categories
     * @return \Magento\Framework\Api\ExtensibleDataInterface
     */
    public function getProductCountByCategories(array $categories) {

        $onlyCount = true;
        return $this->getProductsByCategories($categories, $onlyCount);
    }


}
