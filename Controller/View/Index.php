<?php

/**
 * This file is part of Glugox.
 *
 * (c) Glugox <glugox@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Glugox\PDF\Controller\View;
use Magento\Framework\App\Filesystem\DirectoryList;


/**
 * Controller for pdfs management.
 */
class Index extends \Glugox\PDF\Controller\FrontController {

    /**
     * PDF viewer
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute() {

        $pdfId = (int)$this->getRequest()->getParam('id');
        if($pdfId){
            $pdfModel = $this->_service->get($pdfId);
            if($pdfModel->getPdfFile() && $this->_cache->has($pdfModel->getPdfFile())){
                return $this->_cache->getResult($pdfModel->getName(), $pdfModel->getPdfFile());
            }
        }
        return $this->getResponse()->setRedirect($this->_redirect->getRedirectUrl());
    }
}
