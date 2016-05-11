<?php

/*
 * This file is part of Glugox.
 *
 * (c) Glugox <glugox@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Glugox\PDF\Model;
use Glugox\PDF\Exception\PDFException;
use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Description of PDFResult
 *
 * @author Eko
 */
class PDFResult extends \Magento\Framework\DataObject{



    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;


    /** @var \Glugox\PDF\Helper\Data */
    protected $_pdfHelper;


    /**
     * @var null
     */
    protected $_fileName = null;


    /**
     * @var array|\Magento\Framework\Data\Collection
     */
    protected $_categories = null;

    /**
     * @var array|\Magento\Framework\Data\Collection
     */
    protected $_products = null;


    /**
     * PDFResult constructor.
     * @param array $data
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager, \Glugox\PDF\Helper\Data $helper, array $data=[])
    {
        parent::__construct($data);
        $this->_objectManager = $objectManager;
        $this->_pdfHelper = $helper;
    }


    /**
     * @param array|\Magento\Framework\Data\Collection $category
     */
    public function setCategories($categories){
        $this->_categories = $categories;
    }


    /**
     * @param array|\Magento\Framework\Data\Collection $products
     */
    public function setProducts($products){
        $this->_products = $products;
    }


    /**
     * @var array|null
     */
    protected $_errors = null;

    /**
     *
     * @return null|string
     */
    public function getError() {
        if($this->_errors === null){
            return null;
        }
        return \count($this->_errors) > 0 ? \implode("; ", $this->_errors) : null;
    }

    /**
     * @param string $msg
     * @return \Glugox\PDF\Model\Provider\Products
     */
    public function addError($msg) {

        $this->_errors = null === $this->_errors ? array() : $this->_errors;
        $this->_errors[] = $msg;
        return $this;
    }

    /**
     * @return null
     * @throws PDFException
     */
    public function getFileneme(){
        if(null === $this->_fileName){
            throw new PDFException(__("PDFResult filename must be initialized!"));
        }
        return $this->_fileName;
    }


    /**
     * Creates filename with full relative path for the pdf,
     * that is available in 'getFileneme()' call.
     *
     * TODO: Add filters when on layered cat in filename
     *
     */
    public function createFileName($source){



        $customerId = (int) $this->_pdfHelper->getSession()->getCustomerId();
        $date = $this->_objectManager->get('Magento\Framework\Stdlib\DateTime\DateTime')->date('YmdHis');
        $this->_fileName = \md5($source);


        /*if(strpos($this->_fileName, '-c') === 0){
            $this->_fileName = 'cat-' . \substr($this->_fileName, 2);
        }
        $this->_fileName .= '_u-' . $customerId;
        $this->_fileName .= '_' . $date;*/

        if(!empty(Cache::STORAGE_DIR)){
            $this->_fileName = DirectoryList::VAR_DIR . '/' . PDF::DIRECTORY_PATH . '/' . $this->_fileName . '.pdf';
        }

        return $this->_fileName;
    }

}
