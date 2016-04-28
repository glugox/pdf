<?php
/**
 * This file is part of Glugox.
 *
 * (c) Glugox <glugox@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Glugox\PDF\Controller\Builder;

use Magento\Framework\App\Filesystem\DirectoryList;

class Index extends \Glugox\PDF\Controller\FrontController {


    /**
     * PDF builder
     */
    public function execute() {

        $product = $this->_initProduct();
        if (!$product) {
            $this->messageManager->addNotice(__('Product not found!'));
        }
        
        // Render page
        try {

            $page = $this->_objectManager->create("\Glugox\PDF\Model\Page\Result");
            $this->_productHelper->prepareAndRender($page, $product, $this);
            return $this->_fileFactory->create('test.pdf', $page->getPdf()->render(), DirectoryList::VAR_DIR, 'application/pdf');

        } catch (\Exception $e) {
            die( $e->getMessage() );
        }
    }


}