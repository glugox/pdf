<?php

/*
 * This file is part of Glugox.
 *
 * (c) Glugox <glugox@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Glugox\PDF\Model\Renderer;

use Glugox\PDF\Helper\Config;

/**
 * Description of Products
 *
 * @author Eko
 */
class Products extends \Glugox\PDF\Model\Renderer\AbstractPageRenderer {

    /**
     * Maximum image width from config
     *
     * @var int
     */
    protected $_imageMaxWidth;

    /**
     * Maximum image height from config
     *
     * @var int
     */
    protected $_imageMaxHeight;

    /**
     * Last drawn image height
     *
     * @var int
     */
    protected $_currImageHeight = 0;

    /**
     * Last drawn image width
     *
     * @var int
     */
    protected $_currImageWidth = 0;

    /**
     * Last drawn product item height
     *
     * @var int
     */
    protected $_currItemHeight = 0;

    /**
     * Last drawn image y position
     *
     * @var int
     */
    protected $_currImageY = null;

    /**
     * Maximum position of all elements in the item
     *
     * @var int
     */
    protected $_curritemMaxY = null;

    /**
     * Last drawn image x position
     *
     * @var int
     */
    protected $_currImageX = 0;

    /**
     * This is diferent from _currXFixed becouse
     * it is valid only when we are in item scope
     *
     * @var int
     */
    protected $_currItemXFixed = 0;

    /**
     * Draw product to pdf
     */
    public function draw() {

        // draw header of the page
        parent::draw();
        $this->_drawProductsList()->_addBottomPad();
    }


    /**
     * Initializes page
     */
    protected function _initPage() {

        parent::_initPage();
        $this->_imageMaxWidth = $this->_helper->getConfigObject()->getImageMaxWidthOnProductList();
        $this->_imageMaxHeight = $this->_helper->getConfigObject()->getImageMaxHeightOnProductList();

        // this is bodyPadding untill we draw floating image
        $this->_currX = $this->_currXFixed = $this->_currItemXFixed = $this->_bodyPadding;
    }


    /**
     * Draws product list on the pdf page
     *
     * @return \Glugox\PDF\Model\Renderer\Products
     */
    protected function _drawProductsList() {

        $page = $this->getPage();

        /** @var \Magento\Catalog\Api\Data\ProductInterface[] * */
        $products = $this->getProducts();

        if (\count($products) > 0) {

            foreach ($products as $product) {
                $this->_helper->info(" - Product :: " . $product->getSku());


                $this->_drawProductItem($page, $product);
                //$this->_helper->info(" - Drawn with height :: " . $this->_currItemHeight . " . miny = " . $this->_curritemMaxY);

                /**
                 * Here we will use the last item height to calculate if we need to set new page.
                 * This will not be correct if the next item height is not equal to current.
                 *
                 * TODO: draw the item (maybe without actual drawing) and check if it passed on new page, that remove it and set new page.
                 */
                $page = $this->_checkNewPage($this->_currItemHeight);

                if (!$this->_newPageFlag) {
                    // clearing floats
                    $this->_clearFloats()->_drawHorizontalLine(false)->_addBottomPad();
                }
            }
        }

        return $this;
    }


    /**
     * Draws product list on the pdf page
     *
     * @param \Zend_Pdf_Page $page
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @return \Glugox\PDF\Model\Renderer\Products
     */
    protected function _drawProductItem(\Zend_Pdf_Page $page,
            \Magento\Catalog\Api\Data\ProductInterface $product) {


        $this->_curritemMaxY = $page->getHeight();
        // store y pos to calculate this item's height
        $itemStartY = $this->_currY;

        $this->_drawProductImage($product);
        $this->_drawCategories($product);
        $this->_drawTitle($product);
        $this->_drawPrice($product);
        $this->_drawSku($product);


        if (null !== $this->_curritemMaxY && $this->_curritemMaxY < $page->getHeight() && $this->_currY > $this->_curritemMaxY) {
            $this->_currY = $this->_curritemMaxY;
        }
        $this->_clearItemFloats()->_addBottomPad();

        $this->_currItemHeight = $itemStartY - $this->_currY;



        return $this;
    }


    /**
     * Draws product list image
     *
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @return \Glugox\PDF\Model\Renderer\Product
     */
    protected function _drawProductImage(\Magento\Catalog\Api\Data\ProductInterface $product) {


        if ($this->_helper->getConfigObject()->getShowImageInListMode()) {

            $page = $this->getPage();
            $imagePath = $this->_helper->getProductImage($product);

            // define image resource
            $image = \Zend_Pdf_Image::imageWithPath($imagePath);
            $sizeFactor = $image->getPixelWidth() / $image->getPixelHeight();

            $this->_currImageWidth = $this->_imageMaxWidth;
            $this->_currImageHeight = $this->_currImageWidth / $sizeFactor;

            if ($this->_currImageHeight > $this->_imageMaxHeight) {
                $this->_currImageHeight = $this->_imageMaxHeight;
                $this->_currImageWidth = $sizeFactor * $this->_currImageHeight;
            }

            // write image to page
            $this->_currImageY = $this->_currY - $this->_currImageHeight;
            $page->drawImage($image, $this->_currX, $this->_currImageY, $this->_currX + $this->_currImageWidth, $this->_currImageY + $this->_currImageHeight);
            $this->_currX += $this->_currImageWidth;

            $this->_curritemMaxY = \min($this->_currImageY, $this->_curritemMaxY);

            // float the image
            $this->_currX += $this->_genPadding;

            // set all next elements' x position to the position next to the image
            $this->_currItemXFixed = $this->_currX;



        }

        return $this;
    }


    /**
     * Draw product list categories on the pdf page
     *
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @return \Glugox\PDF\Model\Renderer\Products
     */
    protected function _drawCategories(\Magento\Catalog\Api\Data\ProductInterface $product) {

        if ($this->_helper->getConfigObject()->getDisplayCategories()) {

            $this->_addBottomPad();
            $page = $this->getPage();
            $fontSize = 12;
            $page->setFillColor(new \Zend_Pdf_Color_Html($this->_helper->getConfigObject()->getPdfColor(Config::COLOR_CATEGORIES)));
            $font = $this->_setFontRegular($fontSize);

            $productCategories = $product->getCategoryCollection()
                    ->addAttributeToSelect('url_key')
                    ->addAttributeToSelect('name')
                    ->addAttributeToSelect('url_path');

            $categoryNames = array();
            foreach ($productCategories as $productCategory) {
                $categoryNames[] = $this->prepareTextForDrawing($productCategory->getName());
            }

            $categoriesBreadcrumb = \implode(" > ", $categoryNames);

            $lineHeight = $this->getLineHeight($font, $fontSize);
            //$this->_currY -= $lineHeight;
            $page->drawText($categoriesBreadcrumb, $this->_currX, $this->_currY, 'UTF-8');


            $this->_addBottomPad();
        }

        return $this;
    }


    /**
     * Draw product list name on pthe pdf page
     *
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @return \Glugox\PDF\Model\Renderer\Products
     */
    protected function _drawTitle(\Magento\Catalog\Api\Data\ProductInterface $product) {

        $page = $this->getPage();
        $fontSize = $this->_helper->getConfigObject()->getPdfItemTitleFontSize();
        $maxChars = $this->_helper->getConfigObject()->getPdfItemTitleMaxCharsInLine();
        $title = $product->getName();
        $title = $this->prepareTextForDrawing($title);

        $font = $this->_setFontBold($fontSize);
        $page->setFillColor(new \Zend_Pdf_Color_Html($this->_helper->getConfigObject()->getPdfColor(Config::COLOR_TITLE)));
        $lineHeight = $this->getLineHeight($font, $fontSize);

        $lineSpacingFactor = 1;
        $index = 0;
        foreach ($this->_string->split($title, $maxChars, true, true) as $_value) {
            if ($index > 0) {
                $lineSpacingFactor = 1.5;
            }
            $this->_currY -= $lineSpacingFactor * $lineHeight;
            $page->drawText(
                    trim(strip_tags($_value)), $this->_currX, $this->_currY, 'UTF-8'
            );
            $this->_titleBottomY = $this->_currY;
            ++$index;
        }

        return $this;
    }


    /**
     * Draw product price on the pdf page
     *
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @return \Glugox\PDF\Model\Renderer\Product
     */
    protected function _drawPrice(\Magento\Catalog\Api\Data\ProductInterface $product) {

        if ($this->_helper->getConfigObject()->getDisplayPrice()) {

            $page = $this->getPage();
            $hasDiscount = false;
            $specPriceY = null;

            /**
             * Regular price
             */
            $regularPriceY = null === $this->_titleBottomY ? $this->_currY : $this->_titleBottomY;
            $priceInfoRegular = $this->_helper->getProductPrice($product, 'regular_price');
            $regularPrice = $priceInfoRegular->getValue();
            $regularPriceFormatted = $this->_priceCurrency->format($regularPrice, false);

            /**
             * Discounted price
             */
            $priceInfoFinal = $this->_helper->getProductPrice($product, 'final_price');
            if($priceInfoFinal){
                $finalPrice = $priceInfoFinal->getValue();
                if($finalPrice && $finalPrice < $regularPrice){

                    $hasDiscount = true;
                    $fontSize = 22;
                    $font = $this->_setFontBold($fontSize);
                    $lineHeight = $this->getLineHeight($font, $fontSize);
                    $finalPriceFormatted = $this->_priceCurrency->format($finalPrice, false);
                    $estimatedWidth = $this->getPdf()->widthForStringUsingFontSize($finalPriceFormatted, $font, $fontSize);

                    $page->setFillColor(new \Zend_Pdf_Color_Html($this->_helper->getConfigObject()->getPdfColor(Config::COLOR_PRICE)));


                    $regularPriceY -= $lineHeight;
                    $this->_currX = $page->getWidth() - $estimatedWidth - $this->_bodyPadding;
                    $page->drawText($finalPriceFormatted, $this->_currX, $regularPriceY, 'UTF-8');
                    $this->_curritemMaxY = \min($regularPriceY, $this->_curritemMaxY);
                    $this->_currX += $estimatedWidth;
                    $regularPriceY += $lineHeight;
                }
            }

            /**
             * Drawing
             */
            $fontSize = 22;
            if(!$hasDiscount){
                $fontColor = Config::COLOR_PRICE;
                $font = $this->_setFontBold($fontSize);
            }else{
                $fontColor = Config::COLOR_PRICE_OLD;
                $font = $this->_setFontRegular($fontSize);
                $regularPriceY += 0.5*$lineHeight;
            }
            $page->setFillColor(new \Zend_Pdf_Color_Html($this->_helper->getConfigObject()->getPdfColor($fontColor)));
            $lineHeight = $this->getLineHeight($font, $fontSize);

            $estimatedWidth = $this->getPdf()->widthForStringUsingFontSize($regularPriceFormatted, $font, $fontSize);
            $this->_currX = $page->getWidth() - $estimatedWidth - $this->_bodyPadding;
            $page->drawText($regularPriceFormatted, $this->_currX, $regularPriceY, 'UTF-8');
            $this->_curritemMaxY = \min($regularPriceY, $this->_curritemMaxY);
            $this->_currY = $regularPriceY;

            if($hasDiscount){
                $page->setLineColor(new \Zend_Pdf_Color_Html($this->_helper->getConfigObject()->getPdfColor(Config::COLOR_PRICE_OLD)));
                $page->drawLine($this->_currX, $regularPriceY + 0.4*$lineHeight, $this->_currX + $estimatedWidth, $regularPriceY + 0.4*$lineHeight);
            }

            $this->_currX += $estimatedWidth;



        }

        return $this;
    }


    /**
     * Draw product sku on the pdf page
     *
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @return \Glugox\PDF\Model\Renderer\Product
     */
    protected function _drawSku(\Magento\Catalog\Api\Data\ProductInterface $product) {




        if ($this->_helper->getConfigObject()->getDisplaySku()) {

            $this->_currY = null === $this->_titleBottomY ? $this->_currY : $this->_titleBottomY;
            $this->_clearItemFloats()->_addBottomPad();

            $page = $this->getPage();
            $fontSize = 12;
            $page->setFillColor(new \Zend_Pdf_Color_Html($this->_helper->getConfigObject()->getPdfColor(Config::COLOR_SKU)));
            $font = $this->_setFontRegular($fontSize);

            $this->_currY -= $this->getLineHeight($font, $fontSize);
            $page->drawText(__('SKU') . ': ' . $product->getSku(), $this->_currX, $this->_currY, 'UTF-8');


            $this->_addBottomPad();
        }

        return $this;
    }


    /**
     * Clear all floats in the item scope. Setting the x position to item fixed x
     *
     * @return \Glugox\PDF\Model\Renderer\Products
     */
    protected function _clearItemFloats() {

        $this->_currX = $this->_currItemXFixed;
        return $this;
    }


}
