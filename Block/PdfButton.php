<?php
/**
 * This file is part of Glugox.
 *
 * (c) Glugox <glugox@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Glugox\PDF\Block;

class PdfButton extends PdfBlock
{

    /**
     * Core registry
     *
     * @var \Glugox\PDF\Helper\Data
     */
    protected $_helper;


    /**
     * PdfButton constructor.
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Glugox\PDF\Helper\Data $helper
     * @param array $data
     */
    public function __construct(
            \Magento\Framework\Registry $registry,
            \Magento\Framework\View\Element\Template\Context $context,
            \Glugox\PDF\Helper\Data $helper,
            array $data = array()) {

        $this->_helper = $helper;

        

        parent::__construct($registry, $context, $helper, $data);
    }


    /**
     *
     * @return boolean
     */
    public function canDisplayOnCategory( \Magento\Catalog\Model\Category $category ){
        return $this->_helper->canDisplayOnCategory($category);
    }

    /**
     *
     * @return boolean
     */
    public function canDisplayOnProduct( \Magento\Catalog\Model\Product $product ){
        return $this->_helper->canDisplayOnProduct($product);
    }


    /**
     * Retrieve current product model. It should be called only on category pages.
     * For example: pdf.phtml in catalog product view as it uses request id param for getting it.
     *
     * @return \Magento\Catalog\Model\Product
     */
    public function getProduct()
    {
        $productId = (int)$this->getRequest()->getParam('id', false);
        $this->setProductId($productId);
        return $this->_helper->getRegisteredProduct($this->getProductId());
    }

    /**
     * Retrieve current category model. It should be called only on category pages.
     * For example: pdf.phtml in catalog category view as it uses request id param for getting it.
     *
     * @return \Magento\Catalog\Model\Category
     */
    public function getCategory()
    {
        $categoryId = (int)$this->getRequest()->getParam('id', false);
        $this->setCategoryId($categoryId);
        return $this->_helper->getRegisteredCategory($this->getCategoryId());
    }


    /**
     * Returns number of products that will the print putton execute for printing
     *
     * @param \Magento\Catalog\Model\Category $category
     * @return int
     */
    public function getEstimatePrintProductsOnCategory( \Magento\Catalog\Model\Category $category ){
        return $this->_helper->getEstimatePrintProductsOnCategory($category);
    }

}
