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

use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Framework\DataObject;

/**
 * Render HTML <button> tag.
 *
 */
class Button extends AbstractRenderer
{
    /**
     * {@inheritdoc}
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        /** @var array $attributes */
        $attributes = $this->_prepareAttributes($row);
        return sprintf('<button %s>%s</button>', $this->_getAttributesStr($attributes), $this->_getValue($row));
    }


    /**
     * Whether current item is disabled.
     *
     * @param \Magento\Framework\DataObject $row
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _isDisabled(DataObject $row)
    {
        return false;
    }

    /**
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    protected function _getDisabledAttribute(DataObject $row)
    {
        return $this->_isDisabled($row) ? 'disabled' : '';
    }

    /**
     * Prepare attribute list. Values for attributes gathered from two sources:
     * - If getter method exists in the class - it is taken from there (getter method for "title"
     *   attribute will be "_getTitleAttribute", for "onmouseup" - "_getOnmouseupAttribute" and so on.)
     * - Then it tries to get it from the button's column layout description.
     * If received attribute value is empty - attribute is not added to final HTML.
     *
     * @param \Magento\Framework\DataObject $row
     * @return array
     */
    protected function _prepareAttributes(DataObject $row)
    {
        $attributes = [];
        foreach ($this->_getValidAttributes() as $attributeName) {
            $methodName = sprintf('_get%sAttribute', ucfirst($attributeName));
            $rowMethodName = sprintf('get%s', ucfirst($attributeName));
            $attributeValue = method_exists(
                $this,
                $methodName
            ) ? $this->{$methodName}(
                $row
            ) : $this->getColumn()->{$rowMethodName}();

            if ($attributeValue) {
                $attributes[] = sprintf('%s="%s"', $attributeName, $this->escapeHtml($attributeValue));
            }
        }
        return $attributes;
    }


    /**
     * Get list of available HTML attributes for this element.
     *
     * @return array
     */
    protected function _getValidAttributes()
    {
        /*
         * HTML global attributes - 'accesskey', 'class', 'id', 'lang', 'style', 'tabindex', 'title'
         * HTML mouse event attributes - 'onclick', 'ondblclick', 'onmousedown', 'onmousemove', 'onmouseout',
         *                               'onmouseover', 'onmouseup'
         * Element attributes - 'disabled', 'name', 'type', 'value'
         */
        return [
            'accesskey',
            'class',
            'id',
            'lang',
            'style',
            'tabindex',
            'title',
            'onclick',
            'ondblclick',
            'onmousedown',
            'onmousemove',
            'onmouseout',
            'onmouseover',
            'onmouseup',
            'disabled',
            'name',
            'type',
            'value'
        ];
    }


    /**
     * Get list of attributes rendered as a string (ready to be inserted into tag).
     *
     * @param array $attributes Array of attributes
     * @return string
     */
    protected function _getAttributesStr($attributes)
    {
        return join(' ', $attributes);
    }
}
