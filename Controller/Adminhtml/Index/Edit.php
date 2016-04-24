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

use Magento\Backend\App\Action;
use Glugox\PDF\Block\Adminhtml\PDF\Edit\Tab\Main;
use Glugox\PDF\Exception\PDFException;
use Glugox\PDF\Controller\Adminhtml\Index\Controller;
use Glugox\PDF\Model\PDF as PDFModel;

class Edit extends Controller {

    /**
     * Edit pdf action.
     *
     * @return void
     */
    public function execute() {
        /** Try to recover pdf data from session if it was added during previous request which failed. */
        $pdfId = (int) $this->getRequest()->getParam('id');
        if ($pdfId) {
            try {
                $pdfData = $this->_service->get($pdfId)->getData();
                $originalName = $this->escaper->escapeHtml($pdfData[Main::DATA_NAME]);
            } catch (PDFException $e) {
                $this->messageManager->addError($this->escaper->escapeHtml($e->getMessage()));
                $this->_redirect('*/*/');
                return;
            } catch (\Exception $e) {
                $this->getLogger()->critical($e);
                $this->messageManager->addError(__('Internal error. Check exception log for details.'));
                $this->_redirect('*/*');
                return;
            }
            $restoredPDF = $this->_getSession()->getPDFData();
            if (isset($restoredPDF[Main::DATA_ID]) && $pdfId == $restoredPDF[Main::DATA_ID]) {
                $pdfData = array_merge($pdfData, $restoredPDF);
            }
        } else {
            $this->messageManager->addError(__('PDF ID is not specified or is invalid.'));
            $this->_redirect('*/*/');
            return;
        }
        $this->_registry->register(PDFModel::CURRENT_PDF_KEY, $pdfData);
        $this->_view->loadLayout();
        $this->_getSession()->setPDFData([]);
        $this->_setActiveMenu('Glugox_PDF::pdf');

        $title = __('Edit "%1" PDF', $originalName);

        $this->_addBreadcrumb($title, $title);
        $this->_view->getPage()->getConfig()->getTitle()->prepend($title);
        $this->_view->renderLayout();
    }


}
