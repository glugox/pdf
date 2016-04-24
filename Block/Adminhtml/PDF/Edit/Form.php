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

use Glugox\PDF\Block\Adminhtml\PDF\Edit\Tab\Main;
use Glugox\PDF\Controller\Adminhtml\PDF;
use Glugox\PDF\Model\PDF as PDFModel;

/**
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 */
class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * Prepare form before rendering HTML
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            ['data' => ['id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'post']]
        );
        $pdfData = $this->_coreRegistry->registry(PDFModel::CURRENT_PDF_KEY);
        if (isset($pdfData[Main::DATA_ID])) {
            $form->addField(Main::DATA_ID, 'hidden', ['name' => 'id']);
            $form->setValues($pdfData);
        }
        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }
}
