<?php

/**
 * This file is part of Glugox.
 *
 * (c) Glugox <glugox@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Glugox\PDF\Controller\Adminhtml\Index;

use Glugox\PDF\Exception\PDFException;
use Glugox\PDF\Model\PDF as PDFModel;
use Magento\Framework\App\Filesystem\DirectoryList;

class View extends \Glugox\PDF\Controller\Adminhtml\Index\Controller {


    /**
     * Generates PDF to output
     *
     * @return ResponseInterface|void
     */
    public function execute()
    {
        $pdfId = $this->getRequest()->getParam('pdf_id');
        if ($pdfId) {
            $pdfModel = $this->_service->get($pdfId);
            if ($pdfModel) {
                $pdfResult = $pdfModel->createPdf($this->_service);
                $date = $this->_objectManager->get('Magento\Framework\Stdlib\DateTime\DateTime')->date('Y-m-d_H-i-s');
                return $this->_fileFactory->create(
                    'pdf' . $date . '.pdf',
                    $pdfResult->getPdf()->render(),
                    DirectoryList::VAR_DIR,
                    'application/pdf'
                );
            }
        } else {
            return $this->resultForwardFactory->create()->forward('noroute');
        }
    }

    /**
     * Generates PDF to output
     *
     */
    /*public function execute() {

        $pdfId = (int) $this->getRequest()->getParam('pdf_id');
        try {
            if ($pdfId) {
                $pdfData = $this->_service->get($pdfId);
                if (!$pdfData[Main::DATA_ID]) {
                    $this->messageManager->addError(__('This PDF no longer exists.'));
                } else {
                    //
                    $pdf = $this->_objectManager->create('Magento\Sales\Model\Order\Pdf\Invoice')->getPdf([$invoice]);
                    $date = $this->_objectManager->get('Magento\Framework\Stdlib\DateTime\DateTime')->date('Y-m-d_H-i-s');
                    return $this->_fileFactory->create(
                                    'invoice' . $date . '.pdf', $pdf->render(), DirectoryList::VAR_DIR, 'application/pdf'
                    );
                }
            } else {
                // we are running all pdfs
            }
        } catch (PDFException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->getLogger()->critical($e);
        }
    }*/


}
