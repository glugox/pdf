<?php

/**
 * This file is part of Glugox.
 *
 * (c) Glugox <glugox@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Glugox\PDF\Model;

use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * PDF model.
 *
 * @method \string getPdfFile()
 * @method \Glugox\PDF\Model\PDF setPdfFile(\string $value)
 * @method \int getCustomerId()
 * @method \Glugox\PDF\Model\PDF setCustomerId(\int $value)
 * @method \string getCreatedAt()
 * @method \Glugox\PDF\Model\PDF setCreatedAt(\string $value)
 * @method \string getDownloadUrl()
 * @method \Glugox\PDF\Model\PDF setDownloadUrl(\string $value)
 * @method \string getSourceDefinition()
 * @method \Glugox\PDF\Model\PDF setSourceDefinition(\string $value)
 */
class PDF extends \Magento\Framework\Model\AbstractModel {


    /**
     * @string
     */
    const CURRENT_PDF_KEY = 'current_pdf_key';
    const DIRECTORY_PATH = 'pdf';
    const PDF_ID = 'pdf_id';


    /** @var \Glugox\PDF\Helper\Data */
    protected $_helper;


    protected $_cache;


    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
            \Magento\Framework\Model\Context $context,
            \Magento\Framework\Registry $registry,
            \Glugox\PDF\Helper\Data $helper,
            \Glugox\PDF\Model\Cache $cache,
            \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
            \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
            array $data = []
    ) {

        $this->_helper = $helper;
        $this->_cache = $cache;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }


    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct() {
        parent::_construct();
        $this->_init('Glugox\PDF\Model\ResourceModel\PDF');
    }


    /**
     * @param \Glugox\PDF\Api\PDFServiceInterface $pdfService
     * @return \Glugox\PDF\Model\PDFResult
     */
    public function createPdf(\Glugox\PDF\Api\PDFServiceInterface $pdfService, $processInstanceCode=null){
        $commandString = $this->getSourceDefinition();
        return $pdfService->serve($commandString, null, $processInstanceCode);
    }




    /**
     * Retrieve pdf by index data fields
     *
     * @param type $name
     * @param type $sourceDefinition
     * @param type $customerId
     *
     * @return \Glugox\PDF\Model\PDF
     */
    public function getByIndexData( array $pdfData ){

        $this->_beforeLoad(\implode(",", \array_values($pdfData)), \implode(",", \array_keys($pdfData)));
        $this->_getResource()->getByIndexData($this, $pdfData);
        $this->_afterLoad();
        $this->setOrigData();
        $this->_hasDataChanges = false;

        // update stored data. method is private
        if (isset($this->_data)) {
            $this->storedData = $this->_data;
        } else {
            $this->storedData = [];
        }

        return $this;
    }

    /**
     * Processing manipulation after main transaction commit
     *
     * @return $this
     */
    public function afterDeleteCommit()
    {
        $this->_cache->delete($this->getPdfFile());
        return parent::afterDeleteCommit();
    }


}
