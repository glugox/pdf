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

        $result = ['success' => 0];
        $canGetFormCache = false;
        $isAjax = $this->getRequest()->isXmlHttpRequest();
        $product = $this->_initProduct();

        if (!$product) {
            $this->messageManager->addNotice(__('Product not found!'));
            return false;
        }

        $downloadUrl = $this->_url->getRouteUrl("pdf/product/download", \array_merge($this->_request->getParams(), ['cache_force' => 1]));

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

            $canGetFormCache = $pdfModel->getPdfFile() && $this->_cache->has($pdfModel->getPdfFile(), $this->getRequest()->getParam('cache_force', false));

            /**
             * The main idea is to create the pdf with ajax and save it to the cache.
             * Than we call the same url + 'cache_force' => 1 to get the cached pdf file.
             */
            if($canGetFormCache){
                
                if($isAjax){
                    /**
                     * We are trying to create pdf file and save it to caceh with ajax, but it is already there,
                     * so return the pdf url.
                     */
                    $result['download_url'] = $downloadUrl;
                    $result['success'] = 1;
                    $result['cache'] = 1;
                    $jsonResult = $this->jsonHelper->jsonEncode($result);
                    $this->getResponse()->representJson($jsonResult);
                }else{

                    // display the pdf from cache
                    return $this->_cache->getResult($pdfModel->getName(), $pdfModel->getPdfFile());
                }
                
            }else{

                /**
                 * Create new pdf content for both ajax and non ajax calls
                 */
                $pdfResult = $pdfModel->createPdf($this->_service, $this->getRequest()->getParam('process_instance_code', ''));
                $pdfResult->setProducts([$product]);
                $pdfPath = $pdfResult->getFileneme();
                $pdfModel->setPdfFile($pdfPath)->save();
                $this->_cache->save($pdfResult->getFileneme(), $pdfResult->getPdf()->render());

                if($isAjax){
                    $result['download_url'] = $downloadUrl;
                    $result['success'] = 1;
                    $jsonResult = $this->jsonHelper->jsonEncode($result);
                    $this->getResponse()->representJson($jsonResult);
                }else{
                    return $this->_fileFactory->create($pdfPath, $pdfResult->getPdf()->render(), DirectoryList::ROOT, 'application/pdf');
                }
            }


        }else{
            $this->messageManager->addNotice(__('There was a problem with creating the PDF.'));
        }
    }


}
