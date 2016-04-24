<?php
/**
 * This file is part of Glugox.
 *
 * (c) Glugox <glugox@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Glugox\PDF\Block\Adminhtml;

/**
 * PDF block.
 *
 * @codeCoverageIgnore
 */
class Main extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'adminhtml_pdf';
        $this->_blockGroup = 'Adminhtml_PDF';
        $this->_headerText = __('PDFs');
        $this->_addButtonLabel = __('Add New PDF');
        parent::_construct();
    }
}
