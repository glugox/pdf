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
        }

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
            "name" => "Category: " . $category->getName(),
            "source_definition" => "-c" . $category->getId() . $filtersSourceString,
            "customer_id" => (int) $this->_pdfHelper->getSession()->getCustomerId()
        ]);


        if ($pdfModel->getId()) {

            $pdfResult = null;
            if($pdfModel->getPdfFile() && $this->_cache->has($pdfModel->getPdfFile())){
                return $this->_cache->getResult($pdfModel->getName(), $pdfModel->getPdfFile());
            }else{
                try {
                    $pdfResult = $pdfModel->createPdf($this->_service);
                    $pdfResult->setCategory($category);
                } catch (\Glugox\PDF\Exception\PDFException $pex) {
                    $this->messageManager->addNotice(__($pex->getMessage()));
                } catch (Exception $ex) {
                    $this->messageManager->addNotice(__($ex->getMessage()));
                }
                if ($pdfResult) {
                    $pdfModel->setPdfFile($pdfResult->getFileneme())->save();
                    return $this->_fileFactory->create($pdfResult->getFileneme(), $pdfResult->getPdf()->render(), DirectoryList::ROOT, 'application/pdf');
                }
            }


        } else {
            $this->messageManager->addNotice(__('There was a problem with creating the PDF.'));
        }

        return $this->getResponse()->setRedirect($this->_redirect->getRedirectUrl());
    }


   


}
