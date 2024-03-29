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

/**
 * Description of LayoutPDFProvider
 *
 * @author Eko
 */
class LayoutPDFProvider implements \Glugox\PDF\Model\Provider\PDF\ProviderInterface{


    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var \Glugox\PDF\Helper\ProductPdf
     */
    protected $_productHelper;

    /**
     * @var \Glugox\PDF\Helper\CollectionPdf
     */
    protected $_collectionHelper;

    /**
     * @var \Glugox\PDF\Model\PDFResult
     */
    protected $_pdfResult;


    /**
     * @var \Glugox\PDF\Helper\Data
     */
    protected $_helper;


    /**
     * @var \Glugox\Core\Api\ProcessServiceInterface
     */
    protected $_processService;

    /**
     * LayoutPDFProvider constructor.
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Glugox\PDF\Helper\ProductPdf $productHelper
     * @param \Glugox\PDF\Helper\CollectionPdf $collectionHelper
     * @param \Glugox\PDF\Model\PDFResult $pdfResult
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Glugox\PDF\Helper\ProductPdf $productHelper,
        \Glugox\PDF\Helper\CollectionPdf $collectionHelper,
        \Glugox\PDF\Model\PDFResult $pdfResult,
        \Glugox\PDF\Helper\Data $helper,
        \Glugox\Core\Api\ProcessServiceInterface $processService = null
    )
    {
        $this->_objectManager = $objectManager;
        $this->_productHelper = $productHelper;
        $this->_collectionHelper = $collectionHelper;
        $this->_pdfResult = $pdfResult;
        $this->_helper = $helper;
        $this->_processService = $processService;
    }

    /**
     * Creates the PDF
     * 
     * @param $products
     * @param \Glugox\PDF\Model\PDFResult|null $pdfResult
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function create(
        $products, 
        \Glugox\PDF\Model\PDFResult $pdfResult = null
        ) {

        $this->_products = $products;

        /**
         * Mark the process instance started
         */
        if($this->getProcessService() && $pdfResult->getProcessInstance()){
            $this->getProcessService()->startProcess($pdfResult->getProcessInstance());
        }

        // Page result to keep the pdf instance
        $page = $this->_objectManager->create("\Glugox\PDF\Model\Page\Result");

        // Parse what we are getting, one product or more products
        if (\count($this->_products) === 1) {
            $this->_helper->info("LayoutPDFProvider :: 1 product counted!");
            if( $products instanceof \Magento\Framework\Data\Collection){
                $product =  $products->getFirstItem();
            }else{
                $product = \array_shift($products);
            }

            $this->_helper->info("LayoutPDFProvider :: " . $product->getName());

            // create pdf and pass it to the page result.
            $this->_productHelper->prepareAndRender($page, $product);

        }else{

           // multiple products PDF
            $this->_collectionHelper->prepareAndRender($page, $this->_products);
        }

        
        $pdfResult->setPdf( $page->getPdf() );


        /**
         * Mark the process instance finished
         */
        if($this->getProcessService() && $pdfResult->getProcessInstance()){
            $this->getProcessService()->finishProcess($pdfResult->getProcessInstance(), $pdfResult->getError());
        }

        return $pdfResult;
    }


    /**
     * @return \Glugox\Core\Api\ProcessServiceInterface
     */
    public function getProcessService(){
        return $this->_processService;
    }
}