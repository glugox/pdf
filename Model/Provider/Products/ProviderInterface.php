<?php

/**
 * This file is part of Glugox.
 *
 * (c) Glugox <glugox@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Glugox\PDF\Model\Provider\Products;

interface ProviderInterface {

    /**
     * Returns products by skus
     */
    public function getProductsBySkus(array $skus);

    /**
     * Returns products by categories
     */
    public function getProductsByCategories(array $categories, \Glugox\PDF\Model\PDFResult $pdfResult = null, $onlyCount = false);


    /**
     * Returns all products
     */
    public function getAllProducts();

    /**
     * Returns product by sku
     *
     * @param string $sku
     */
    public function getProductBySku($sku);

    /**
     * Returns product by id
     *
     * @param string $id
     */
    public function getProductById($id);

    /**
     * @return false|string
     */
    public function getError();

    /**
     * Returns number of products to be executed by categories
     *
     * @param array $categories
     * @return \Magento\Framework\Api\ExtensibleDataInterface
     */
    public function getProductCountByCategories(array $categories);

    /**
     * Avoiding circular dependency.
     * Required to be set on init.
     *
     * @param \Glugox\PDF\Helper\Data $helper
     * @return \Glugox\PDF\Model\Provider\Products
     */
    public function setHelper(\Glugox\PDF\Helper\Data $helper);
}
