<?php

/**
 * This file is part of Glugox.
 *
 * (c) Glugox <glugox@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Glugox\PDF\Controller;

/**
 * Controller for pdfs management.
 */
abstract class FrontController extends  \Magento\Framework\App\Action\Action {

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_registry;


    /** @var \Glugox\PDF\Helper\Data */
    protected $_pdfHelper;

    /** @var \Glugox\PDF\Api\PDFServiceInterface */
    protected $_service;

    /** @var \Magento\Framework\Json\Helper\Data */
    protected $jsonHelper;

    /**
     * @var \Magento\Framework\Escaper
     */
    protected $escaper;

    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    protected $_fileFactory;

    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator
     */
    protected $_formKeyValidator;

    /**
     * @var \Glugox\PDF\Model\PDF
     */
    protected $pdf;

    /**
     * @var  \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var  \Magento\Catalog\Api\CategoryRepositoryInterface
     */
    protected $categoryRepository;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var \Glugox\PDF\Helper\ProductPdf
     */
    protected $_productHelper;


    /**
     * @var \Glugox\PDF\Model\Cache
     */
    protected $_cache;


    /**
     * @var \Magento\Catalog\Model\Layer\Category\FilterableAttributeList
     */
    protected $_attributesList;


    /**
     * FrontController constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Glugox\PDF\Helper\Data $pdfHelper
     * @param \Glugox\PDF\Api\PDFServiceInterface $service
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magento\Framework\Escaper $escaper
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     * @param \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
     * @param \Glugox\PDF\Model\PDF $pdf
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Glugox\PDF\Helper\ProductPdf $productHelper
     */
    public function __construct(
    \Magento\Backend\App\Action\Context $context,
            \Magento\Framework\Registry $registry,
            \Glugox\PDF\Helper\Data $pdfHelper,
            \Glugox\PDF\Api\PDFServiceInterface $service,
            \Magento\Framework\Json\Helper\Data $jsonHelper,
            \Magento\Framework\Escaper $escaper,
            \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
            \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
            \Glugox\PDF\Model\PDF $pdf,
            \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
            \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository,
            \Magento\Store\Model\StoreManagerInterface $storeManager,
            \Magento\Framework\ObjectManagerInterface $objectManager,
            \Glugox\PDF\Helper\ProductPdf $productHelper,
            \Glugox\PDF\Model\Cache $cache,
            \Magento\Catalog\Model\Layer\Category\FilterableAttributeList $attributesList
    ) {
        parent::__construct($context);
        $this->_registry = $registry;
        $this->_pdfHelper = $pdfHelper;
        $this->_service = $service;
        $this->jsonHelper = $jsonHelper;
        $this->escaper = $escaper;
        $this->_fileFactory = $fileFactory;
        $this->_formKeyValidator = $formKeyValidator;
        $this->pdf = $pdf;
        $this->productRepository = $productRepository;
        $this->categoryRepository = $categoryRepository;
        $this->_storeManager = $storeManager;
        $this->_objectManager = $objectManager;
        $this->_productHelper = $productHelper;
        $this->_cache = $cache;
        $this->_attributesList = $attributesList;
    }




    /**
     * @return \Psr\Log\LoggerInterface
     */
    protected function getLogger() {
        return $this->_pdfHelper->getLogger();
    }


    /**
     * Initialize Product Instance
     *
     * @return \Magento\Catalog\Model\Product
     */
    protected function _initProduct()
    {
        $productId = (int)$this->getRequest()->getParam('id');
        if (!$productId) {
            return false;
        }
        try {
            $product = $this->productRepository->getById($productId);
            if (!$product->isVisibleInCatalog()) {
                return false;
            }
        } catch (NoSuchEntityException $noEntityException) {
            return false;
        }

        $this->_registry->register('pdf_product', $product);
        return $product;
    }


    /**
     * Initialize Category Instance
     *
     * @return \Magento\Catalog\Model\Category
     */
    protected function _initCategory()
    {
        $categoryId = (int)$this->getRequest()->getParam('id');
        if (!$categoryId) {
            return false;
        }
        try {
            $category = $this->categoryRepository->get($categoryId, $this->_storeManager->getStore()->getId());
        } catch (NoSuchEntityException $noEntityException) {
            return false;
        }
        if (!$this->_objectManager->get('Magento\Catalog\Helper\Category')->canShow($category)) {
            return false;
        }
        
        $this->_registry->register('pdf_category', $category);
        return $category;
    }


}
