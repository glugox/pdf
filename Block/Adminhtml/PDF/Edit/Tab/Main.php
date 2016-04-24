<?php

/**
 * This file is part of Glugox.
 *
 * (c) Glugox <glugox@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Glugox\PDF\Block\Adminhtml\PDF\Edit\Tab;

use Glugox\PDF\Model\PDF as PDFModel;

/**
 * Main PDF info edit form
 *
 */
class Main extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface {

    /*
     * Form elements names.
     */
    const HTML_ID_PREFIX = 'glugox_pdf_properties_';
    const DATA_ID = 'pdf_id';
    const DATA_NAME = 'name';
    const DATA_SOURCE_DEFINITION = 'source_definition';
    const DATA_PDF_URL = 'pdf_url';
    const DATA_PDF_FILE = 'pdf_file';


    /**
     * Set form id prefix, declare fields for pdf info
     *
     * @return $this
     */
    protected function _prepareForm() {
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix(self::HTML_ID_PREFIX);
        $pdfData = $this->_coreRegistry->registry(PDFModel::CURRENT_PDF_KEY);
        $this->_addGeneralFieldset($form, $pdfData);
        $form->setValues($pdfData);
        $this->setForm($form);
        return $this;
    }


    /**
     * Prepare label for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel() {
        return __('PDF Info');
    }


    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle() {
        return $this->getTabLabel();
    }


    /**
     * Returns status flag about this tab can be showen or not
     *
     * @return true
     */
    public function canShowTab() {
        return true;
    }


    /**
     * Returns status flag about this tab hidden or not
     *
     * @return true
     */
    public function isHidden() {
        return false;
    }


    /**
     * Add fieldset with general pdf information.
     *
     * @param \Magento\Framework\Data\Form $form
     * @param array $pdfData
     * @return void
     */
    protected function _addGeneralFieldset($form, $pdfData) {
        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('General')]);


        $disabled = true;
        if (isset($pdfData[self::DATA_ID])) {
            $fieldset->addField(self::DATA_ID, 'hidden', ['name' => 'id']);
        }


        $fieldset->addField(
                self::DATA_NAME, 'text', [
            'label' => __('Name'),
            'name' => self::DATA_NAME,
            'required' => true,
            'disabled' => false,
            'maxlength' => '255'
                ]
        );


        $fieldset->addField(
                self::DATA_SOURCE_DEFINITION, 'text', [
            'label' => __('Source Definition'),
            'name' => self::DATA_SOURCE_DEFINITION,
            'required' => true,
            'disabled' => false,
            'maxlength' => '255',
                    'note' => __(
                    'Definition of what is to be written to the pdf.Use "glugox:pdf:create --help" command to see available arguments/options.'
            )
                ]
        );


        $fieldset->addField(
                self::DATA_PDF_URL, 'text', [
            'label' => __('PDF URL'),
            'name' => self::DATA_PDF_URL,
            'required' => false,
            'disabled' => $disabled,
            'note' => __(
                    'PDF URL if it is cached'
            )
                ]
        );

        $fieldset->addField(
                self::DATA_PDF_FILE, 'text', [
            'label' => __('PDF file'),
            'name' => self::DATA_PDF_FILE,
            'required' => false,
            'disabled' => $disabled,
            'maxlength' => '255',
            'note' => __(
                    'PDF file if it is cached'
            )
                ]
        );

    }


}
