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
use Glugox\PDF\Controller\Adminhtml\Index\Controller;
use Glugox\PDF\Exception\PDFException;

class Save extends Controller {

    /**
     * Redirect  to edit or new if error happened during pdf save.
     *
     * @return void
     */
    protected function _redirectOnSaveError() {
        $pdfId = $this->getRequest()->getParam('id');
        if ($pdfId) {
            $this->_redirect('*/*/edit', ['id' => $pdfId]);
        } else {
            $this->_redirect('*/*/new');
        }
    }


    /**
     * Save pdf action.
     *
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute() {
        /** @var array $pdfData */
        $pdfData = [];
        try {
            $pdfId = (int) $this->getRequest()->getParam('id');
            if ($pdfId) {
                try {
                    $pdfData = $this->_service->get($pdfId)->getData();
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
            }
            /** @var array $data */
            $data = $this->getRequest()->getPostValue();
            if (!empty($data)) {
                if (!isset($data['resource'])) {
                    $pdfData['resource'] = [];
                }
                $pdfData = array_merge($pdfData, $data);
                if (!isset($pdfData[Main::DATA_ID])) {
                    $pdf = $this->_service->create($pdfData);
                } else {
                    $pdf = $this->_service->update($pdfData);
                }
                if (!$this->getRequest()->isXmlHttpRequest()) {
                    $this->messageManager->addSuccess(
                            __(
                                    'The pdf \'%1\' has been saved.', $this->escaper->escapeHtml($pdf->getName())
                            )
                    );
                }
                if ($this->getRequest()->isXmlHttpRequest()) {
                    $isTokenExchange = $pdf->getEndpoint() && $pdf->getIdentityLinkUrl() ? '1' : '0';
                    $this->getResponse()->representJson(
                            $this->jsonHelper->jsonEncode(
                                    ['pdfId' => $pdf->getId(), 'isTokenExchange' => $isTokenExchange]
                            )
                    );
                } else {
                    $this->_redirect('*/*/');
                }
            } else {
                $this->messageManager->addError(__('The pdf was not saved.'));
            }
        } catch (PDFException $e) {
            $this->messageManager->addError($this->escaper->escapeHtml($e->getMessage()));
            $this->_getSession()->setPdfData($pdfData);
            $this->_redirectOnSaveError();
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($this->escaper->escapeHtml($e->getMessage()));
            $this->_redirectOnSaveError();
        } catch (\Exception $e) {
            $this->getLogger()->critical($e);
            $this->messageManager->addError($this->escaper->escapeHtml($e->getMessage()));
            $this->_redirectOnSaveError();
        }
    }


}
