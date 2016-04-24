<?php
/**
 * This file is part of Glugox.
 *
 * (c) Glugox <glugox@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Glugox\PDF\Block\Adminhtml\PDF\Edit;

class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    /**
     * Initialize pdf edit page tabs
     *
     * @return void
     * @codeCoverageIgnore
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('glugox_pdf_edit_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Basic Settings'));
    }
}
