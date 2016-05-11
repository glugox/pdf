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

use Glugox\PDF\Model\Page\Config;
use Magento\Framework\App\Helper\AbstractHelper;
use Glugox\PDF\Model\Page\Result;
use Magento\Catalog\Model\Product;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Helper\Context;


class ProductPdf extends AbstractHelper
{



    /**
     * @var \Glugox\PDF\Helper\Data
     */
    protected $_helper;


    public function __construct(Context $context, \Glugox\PDF\Helper\Data $helper)
    {
        parent::__construct($context);
        $this->_helper = $helper;
    }

    /**
     * Prepares product pdf page
     *
     * @param \Glugox\PDF\Model\Page\Result $resultPage
     * @param Product $product
     * @param \Magento\Framework\App\Action\Action $controller
     * @return \Glugox\PDF\Helper\ProductPdf
     */
    public function prepareAndRender(Result $resultPage, Product $product)
    {

        $this->_helper->info("ProductPdf helper ::: prepareAndRender " . $product->getName());

        $pdf = $resultPage->getConfig()
            ->setProduct($product)
            ->render();


        $resultPage->setPdf($pdf);

        return $this;
    }

}