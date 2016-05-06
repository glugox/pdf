<?php

/**
 * This file is part of Glugox.
 *
 * (c) Glugox <glugox@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Glugox\PDF\Controller\Product;

use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Controller for pdfs management.
 */
class Download extends \Glugox\PDF\Controller\FrontController {

    /**
     * PDF download
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute() {
        $product = $this->_initProduct();

        if (!$product) {
            $this->messageManager->addNotice(__('Product not found!'));
        }

        $pdfModel = $this->_service->getOrCreate([
            "name" => "Product: " . $product->getName(),
            "source_definition" => $product->getSku(),
            "customer_id" => (int) $this->_pdfHelper->getSession()->getCustomerId()
        ]);



        $pdfName = 'product-' . $product->getSku() . '_' . (int)$this->_pdfHelper->getSession()->getCustomerId();

        if ($pdfModel->getId()) {
            /**
             * PDF Model is magento db model type that stores information about the generated pdf.
             * It calls the PDFService which gets requested products chooses pdf provider to generate the PDF (Zend_Pdf) instance
             * with render() method.
             */
            $pdfResult = $pdfModel->createPdf($this->_service);
            //$date = $this->_objectManager->get('Magento\Framework\Stdlib\DateTime\DateTime')->date('Y-m-d_H-i-s');
            return $this->_fileFactory->create($pdfName . '.pdf', $pdfResult->getPdf()->render(), DirectoryList::VAR_DIR, 'application/pdf');
        }else{
            $this->messageManager->addNotice(__('There was a problem with creating the PDF.'));
        }
    }


}
