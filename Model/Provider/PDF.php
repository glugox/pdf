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

use Glugox\PDF\Model\PDFResult;
use Glugox\PDF\Model\Provider\PDF\AbstractPdf;
use Glugox\PDF\Exception\PDFException;

/**
 * Description of PDF
 *
 * @author Glugox
 */
class PDF extends AbstractPdf implements \Glugox\PDF\Model\Provider\PDF\ProviderInterface {

    /**
     * products|product
     *
     * @var string
     */
    protected $_rendererType;

    /**
     * Products for PDF
     *
     * @var array
     */
    protected $_products;

    /**
     * Creates the PDF
     *
     * @param type $products
     * @param PDFResult $pdfResult
     * @return type
     */
    public function create($products,\Glugox\PDF\Model\PDFResult $pdfResult = null) {

        $this->_products = $products;
        $pdfResult = null === $pdfResult ? $this->_helper->createPdfResult() : $pdfResult;

        $pdf = $this->getPdf();
        $pdfResult->setPdf($pdf);

        $this->_helper->info("PDF::create Returning : " . \get_class($pdfResult));

        return $pdfResult;
    }


    /**
     * @return \Zend_Pdf
     */
    public function getPdf() {

        $this->_beforeGetPdf();
        $this->_rendererType = \count($this->_products) > 1 ? 'products' : 'product';
        $this->_initRenderer($this->_rendererType);

        $pdf = new \Zend_Pdf();
        $this->_setPdf($pdf);

        $page = $this->newPage();
        if (\count($this->_products) === 1) {
            if( $this->_products instanceof \Magento\Framework\Data\Collection){
                $product =  $this->_products->getFirstItem();
            }else{
                $product = \array_shift($this->_products);
            }

            $page = $this->_drawProduct($page, $product);
        } else if (\count($this->_products) > 1) {
            $page = $this->_drawProducts($page, $this->_products);

        } else {
            throw new PDFException(__("Products not provided!"));
        }

        $this->_afterGetPdf();

        return $pdf;
    }


}
