<?php

/**
 * This file is part of Glugox.
 *
 * (c) Glugox <glugox@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Glugox\PDF\Block\Adminhtml\PDF;

use Glugox\PDF\Block\Adminhtml\PDF\Edit\Tab\Main;
use Glugox\PDF\Controller\Adminhtml\PDF;
use Glugox\PDF\Model\PDF as PDFModel;

class Edit extends \Magento\Backend\Block\Widget\Form\Container {

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_registry = null;

    /**
     * Initialize dependencies.
     *
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
    \Magento\Backend\Block\Widget\Context $context,
            \Magento\Framework\Registry $registry, array $data = []
    ) {
        $this->_registry = $registry;
        parent::__construct($context, $data);
    }


    /**
     * Initialize PDF edit page
     *
     * @return void
     */
    protected function _construct() {
        $this->_controller = 'adminhtml_pdf';
        $this->_blockGroup = 'Glugox_PDF';
        parent::_construct();
        $this->buttonList->remove('reset');
        //$this->buttonList->remove('delete');


        if ($this->_isNewPDF()) {
            $this->removeButton(
                    'save'
            )->addButton(
                    'save', [
                'id' => 'save-split-button',
                'label' => __('Save'),
                'class_name' => 'Magento\Backend\Block\Widget\Button\SplitButton',
                'button_class' => '',
                'data_attribute' => [
                    'mage-init' => ['button' => ['event' => 'save', 'target' => '#edit_form']],
                ],
                'options' => [
                    'save_activate' => [
                        'id' => 'activate',
                        'label' => __('Save & Activate'),
                        'data_attribute' => [
                            'mage-init' => [
                                'button' => ['event' => 'saveAndActivate', 'target' => '#edit_form'],
                                'glugoxPDF' => ['gridUrl' => $this->getUrl('*/*/')],
                            ],
                        ],
                    ],
                ]
                    ]
            );
        }

        $this->buttonList->add(
                'view_pdf', [
            'label' => __('View PDF'),
            'class' => 'view-pdf',
            'onclick' => 'setLocation(\'' . $this->getViewPdfUrl() . '\')'
                ]
        );
    }


    /**
     * Get header text for edit page.
     *
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText() {
        if ($this->_isNewPDF()) {
            return __('New PDF');
        } else {
            return __(
                    "Edit PDF '%1'", $this->escapeHtml(
                            $this->_registry->registry(PDFModel::CURRENT_PDF_KEY)[Main::DATA_NAME]
                    )
            );
        }
    }


    /**
     * {@inheritdoc}
     */
    public function getFormActionUrl() {
        return $this->getUrl('*/*/save');
    }


    /**
     * Get view pdf url
     *
     * @return string
     */
    public function getViewPdfUrl()
    {
        return $this->getUrl('glugox_pdf/*/view', ['pdf_id' => $this->_registry->registry(PDFModel::CURRENT_PDF_KEY)[Main::DATA_ID]]);
    }

    /**
     * Determine whether we create new pdf or editing an existing one.
     *
     * @return bool
     */
    protected function _isNewPDF() {
        return !isset($this->_registry->registry(PDFModel::CURRENT_PDF_KEY)[Main::DATA_ID]);
    }


}
