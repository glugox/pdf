<?php
/**
 * This file is part of Glugox.
 *
 * (c) Glugox <glugox@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Glugox\PDF\Helper;

use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Framework\App\Helper\AbstractHelper;
use Glugox\PDF\Model\Page\Result;
use Magento\Catalog\Model\Product;
use Magento\Framework\App\Action\Action;


class CollectionPdf extends AbstractHelper
{

    /**
     * Prepares product pdf page
     *
     * @param \Glugox\PDF\Model\Page\Result $resultPage
     * @param Product $product
     * @param \Magento\Framework\App\Action\Action $controller
     * @return \Glugox\PDF\Helper\ProductPdf
     */
    public function prepareAndRender(Result $resultPage, Collection $collection)
    {
        
        $pdf = $resultPage->getConfig()
            ->setProductCollection($collection)
            ->render();

        $resultPage->setPdf($pdf);

        return $this;
    }

}