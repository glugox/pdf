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

use Magento\Framework\App\Helper\AbstractHelper;
use Glugox\PDF\Model\Page\Result;
use Magento\Catalog\Model\Product;
use Magento\Framework\App\Action\Action;


class ProductPdf extends AbstractHelper
{

    /**
     * Prepares product pdf page
     *
     * @param \Glugox\PDF\Model\Page\Result $resultPage
     * @param Product $product
     * @param \Magento\Framework\App\Action\Action $controller
     * @return \Glugox\PDF\Helper\ProductPdf
     */
    public function prepareAndRender(Result $resultPage, Product $product, Action $controller)
    {
        $pageConfig = $resultPage->getConfig();

        /** @var \Glugox\PDF\Model\Renderer\RootRendererInterface $rootRenderer */
        $rootRenderer = $pageConfig->getLayout()->getRootRenderer();

        $pdf = $rootRenderer->initialize($pageConfig)
            //->setDebugMode(true)
            ->render();

        $resultPage->setPdf($pdf);

        return $this;
    }

}