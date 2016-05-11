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
abstract class Controller extends Action {

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_registry;


    /** @var \Glugox\PDF\Helper\Data */
    protected $_pdfHelper;

    /** @var \Glugox\PDF\Api\PDFServiceInterface */
    protected $_service;

    /** @var \Magento\Framework\Json\Helper\Data */
    protected $jsonHelper;

    /**
     * @var \Magento\Framework\Escaper
     */
    protected $escaper;

    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    protected $_fileFactory;


    /**
     * @var \Glugox\PDF\Model\Cache
     */
    protected $_cache;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Glugox\PDF\Helper\Data $helper
     * @param \Glugox\PDF\Api\PDFServiceInterface $service
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magento\Framework\Escaper $escaper
     */
    public function __construct(
    \Magento\Backend\App\Action\Context $context,
            \Magento\Framework\Registry $registry,
            \Glugox\PDF\Helper\Data $pdfHelper,
            \Glugox\PDF\Api\PDFServiceInterface $service,
            \Magento\Framework\Json\Helper\Data $jsonHelper,
            \Magento\Framework\Escaper $escaper,
            \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
            \Glugox\PDF\Model\Cache $cache
    ) {
        parent::__construct($context);
        $this->_registry = $registry;
        $this->_pdfHelper = $pdfHelper;
        $this->_service = $service;
        $this->jsonHelper = $jsonHelper;
        $this->escaper = $escaper;
        $this->_fileFactory = $fileFactory;
        $this->_cache = $cache;
    }


    /**
     * Check ACL.
     *
     * @return boolean
     */
    protected function _isAllowed() {
        return $this->_authorization->isAllowed('Glugox_PDF::pdf');
    }


    /**
     * @return \Psr\Log\LoggerInterface
     */
    protected function getLogger() {
        return $this->_pdfHelper->getLogger();
    }


}
