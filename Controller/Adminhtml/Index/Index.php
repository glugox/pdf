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

/**
 * Controller for pdfs management.
 */
class Index extends Controller
{

    /**
     * PDFs grid.
     *
     * @return void
     */
    public function execute()
    {

        $this->_view->loadLayout();
        $this->_setActiveMenu('Glugox_PDF::pdf');
        $this->_addBreadcrumb(__('Glugox PDF'), __('PDF'));
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('PDF'));
        $this->_view->renderLayout();
    }
}
