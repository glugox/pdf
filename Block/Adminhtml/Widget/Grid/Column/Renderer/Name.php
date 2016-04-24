<?php
/**
 * This file is part of Glugox.
 *
 * (c) Glugox <glugox@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Glugox\PDF\Block\Adminhtml\Widget\Grid\Column\Renderer;

/**
 * PDF Name Renderer
 */
class Name extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Text
{
    /**
     * Render pdf name.
     *
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        /** @var \Magento\PDF\Model\PDF $row */
        $text = parent::render($row);
        return $text;
    }

}
