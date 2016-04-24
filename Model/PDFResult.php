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
/**
 * Description of PDFResult
 *
 * @author Eko
 */
class PDFResult extends \Magento\Framework\DataObject{


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

}
