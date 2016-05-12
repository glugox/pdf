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

use Glugox\PDF\Model\PDF;
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
            "name" => $product->getName(),
            "source_definition" => $product->getSku(),
            "customer_id" => (int) $this->_pdfHelper->getSession()->getCustomerId()
        ]);


        /**
         * PDF Model is magento db model type that stores information about the generated pdf.
         * It calls the PDFService which gets requested products chooses pdf provider to generate the PDF (Zend_Pdf) instance
         * with render() method.
         */
        if ($pdfModel->getId()) {

            if($pdfModel->getPdfFile() && $this->_cache->has($pdfModel->getPdfFile())){
                return $this->_cache->getResult($pdfModel->getName(), $pdfModel->getPdfFile());
            }else{
                $pdfResult = $pdfModel->createPdf($this->_service);
                $pdfResult->setProducts([$product]);
                $pdfPath = $pdfResult->getFileneme();
                $pdfModel->setPdfFile($pdfPath)->save();
                return $this->_fileFactory->create($pdfPath, $pdfResult->getPdf()->render(), DirectoryList::ROOT, 'application/pdf');
            }


        }else{
            $this->messageManager->addNotice(__('There was a problem with creating the PDF.'));
        }
    }


}
