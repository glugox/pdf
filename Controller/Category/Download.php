<?php

/**
 * This file is part of Glugox.
 *
 * (c) Glugox <glugox@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Glugox\PDF\Controller\Category;

use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Controller for pdfs management.
 */
class Download extends \Glugox\PDF\Controller\FrontController {


    /**
     * Catalog layer
     *
     * @var \Magento\Catalog\Model\Layer
     */
    protected $_catalogLayer;

    
    
    /**
     * PDF download
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute() {

        /**
         * Check category
         */
        $category = $this->_initCategory();
        if (!$category) {
            $this->messageManager->addNotice(__('Category not found!'));
            return false;
        }

        $result = ['success' => 0];

        $downloadUrl = $this->_url->getRouteUrl("pdf/category/download", \array_merge($this->_request->getParams(), ['cache_force' => 1]));
        $canGetFormCache = false;
        $isAjax = $this->getRequest()->isXmlHttpRequest();
        $pdfResult = null;

        /**
         * Layer filters
         */
        $filtersUsed = [];
        $filtersSourceString = '';
        /** @var \Magento\Catalog\Model\ResourceModel\Eav\Attribute $filter */
        foreach ($this->_attributesList->getList() as $filter) {
            $reqKey = $filter->getAttributeCode();
            if($reqVal = $this->_request->getParam($reqKey)){
                $filtersUsed[] = ['key'=>$reqKey, 'val'=>$reqVal];
            }
        }
        \usort($filtersUsed, function($a, $b){
            return $a['key'] == $b['key'] ? 0 : ( $a['key'] < $b['key'] ? -1 : 1  );
        });

        if(\count($filtersUsed)){
            $filtersSourceString = ' -f"';
            foreach ($filtersUsed as $item) {
                $filtersSourceString .= ($item['key'].'='.$item['val']) . ',';
            }
            $filtersSourceString = \trim($filtersSourceString, ',') . '"';
        }


        $pdfModel = $this->_service->getOrCreate([
            "name" => $category->getName(),
            "source_definition" => "-c" . $category->getId() . $filtersSourceString,
            "customer_id" => (int) $this->_pdfHelper->getSession()->getCustomerId()
        ]);




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

            }else {

                /**
                 * Create new pdf content for both ajax and non ajax calls
                 */
                try {
                    $pdfResult = $pdfModel->createPdf($this->_service, $this->getRequest()->getParam('process_instance_code', ''));
                    $pdfResult->setCategories([$category]);
                } catch (\Glugox\PDF\Exception\PDFException $pex) {
                    $this->messageManager->addNotice(__($pex->getMessage()));
                    $result['error'] = __($pex->getMessage());
                } catch (Exception $ex) {
                    $this->messageManager->addNotice(__($ex->getMessage()));
                    $result['error'] = __($ex->getMessage());
                }

                if (!$pdfResult) {
                    return false;
                }


                $pdfModel->setPdfFile($pdfResult->getFileneme())->save();
                $this->_cache->save($pdfResult->getFileneme(), $pdfResult->getPdf()->render());

                if ($isAjax) {
                    $result['download_url'] = $downloadUrl;
                    $result['success'] = 1;
                    $jsonResult = $this->jsonHelper->jsonEncode($result);
                    $this->getResponse()->representJson($jsonResult);
                } else {
                    return $this->_fileFactory->create($pdfResult->getFileneme(), $pdfResult->getPdf()->render(), DirectoryList::ROOT, 'application/pdf');
                }
            }


        } else {
            $this->messageManager->addNotice(__('There was a problem with creating the PDF.'));
        }
    }


   


}
