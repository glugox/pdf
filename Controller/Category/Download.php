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
     * PDF download
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute() {
        $category = $this->_initCategory();

        if (!$category) {
            $this->messageManager->addNotice(__('Category not found!'));
        }


        $pdfModel = $this->_service->getOrCreate([
            "name" => "Category: " . $category->getName(),
            "source_definition" => "-c" . $category->getId(),
            "customer_id" => (int) $this->_pdfHelper->getSession()->getCustomerId()
        ]);

        $pdfName = 'category-' . $category->getId() . '_' . (int) $this->_pdfHelper->getSession()->getCustomerId();

        if ($pdfModel->getId()) {

            $pdfResult = null;
            try {
                $pdfResult = $pdfModel->createPdf($this->_service);
            } catch (\Glugox\PDF\Exception\PDFException $pex) {
                $this->messageManager->addNotice(__($pex->getMessage()));
            } catch (Exception $ex) {
                $this->messageManager->addNotice(__($ex->getMessage()));
            }

            if ($pdfResult) {
                //$date = $this->_objectManager->get('Magento\Framework\Stdlib\DateTime\DateTime')->date('Y-m-d_H-i-s');
                return $this->_fileFactory->create($pdfName . '.pdf', $pdfResult->getPdf()->render(), DirectoryList::VAR_DIR, 'application/pdf');
            }
        } else {
            $this->messageManager->addNotice(__('There was a problem with creating the PDF.'));
        }

        return $this->getResponse()->setRedirect($this->_redirect->getRedirectUrl());
    }


}
