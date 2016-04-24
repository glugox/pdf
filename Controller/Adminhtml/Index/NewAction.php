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

use Glugox\PDF\Model\PDF as PDFModel;
use Glugox\PDF\Controller\Adminhtml\Index\Controller;

class NewAction extends Controller
{
    /**
     * New pdf action.
     *
     * @return void
     */
    public function execute()
    {
        $this->_view->loadLayout();
        $this->_setActiveMenu('Glugox_PDF::pdf');
        $this->_addBreadcrumb(__('New PDF'), __('New PDF'));
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('New PDF'));
        /** Try to recover pdf data from session if it was added during previous request which failed. */
        $restoredPDF = $this->_getSession()->getPdfData();
        if ($restoredPDF) {
            $this->_registry->register(PDFModel::CURRENT_PDF_KEY, $restoredPDF);
            $this->_getSession()->setPdfData([]);
        }
        $this->_view->renderLayout();
    }
}
