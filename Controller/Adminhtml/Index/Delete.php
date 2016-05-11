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

use Glugox\PDF\Block\Adminhtml\PDF\Edit\Tab\Main;
use Glugox\PDF\Exception\PDFException;
use Magento\Framework\Controller\ResultFactory;
use Glugox\PDF\Model\PDF as PDFModel;

class Delete extends \Glugox\PDF\Controller\Adminhtml\Index\Controller {

    /**
     * Delete the pdf.
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute() {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $pdfId = (int) $this->getRequest()->getParam('id');
        try {
            if ($pdfId) {
                $pdfData = $this->_service->delete($pdfId);
                if (!$pdfData[Main::DATA_ID]) {
                    $this->messageManager->addError(__('This pdf no longer exists.'));
                } else {
                    $this->_cache->delete($pdfData["pdf_file"]);
                    $this->_registry->register(PDFModel::CURRENT_PDF_KEY, $pdfData);
                    $this->messageManager->addSuccess(
                            __(
                                    "The pdf '%1' has been deleted.", $this->escaper->escapeHtml($pdfData[Main::DATA_NAME])
                            )
                    );
                }
            } else {
                $this->messageManager->addError(__('PDF ID is not specified or is invalid.'));
            }
        } catch (PDFException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->getLogger()->critical($e);
        }

        return $resultRedirect->setPath('*/*/');
    }


}
