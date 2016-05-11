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

use Glugox\PDF\Exception\PDFException;
use Glugox\PDF\Model\PDF as PDFModel;
use Magento\Framework\App\Filesystem\DirectoryList;

class View extends \Glugox\PDF\Controller\Adminhtml\Index\Controller {


    /**
     * Generates PDF to output
     *
     * @return ResponseInterface|void
     */
    public function execute()
    {
        $pdfId = $this->getRequest()->getParam('pdf_id');
        if ($pdfId) {

            /** @var PDFModel $pdfModel */
            $pdfModel = $this->_service->get($pdfId);
            if ($pdfModel) {
                if($this->_cache->has($pdfModel->getPdfFile())){
                    return $this->_cache->getResult($pdfModel->getName(),$pdfModel->getPdfFile());
                }
            }
        } else {
            return $this->resultForwardFactory->create()->forward('noroute');
        }
    }




}
