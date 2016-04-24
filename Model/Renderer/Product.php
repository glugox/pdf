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
 * Description of Product
 *
 * @author Eko
 */
class Product extends \Glugox\PDF\Model\Renderer\AbstractPageRenderer {

    /**
     * Draw product to pdf
     */
    public function draw() {


        // draw header of the page
        parent::draw();

        $this->_drawCategories();
        $this->_drawTitle();

        // floading sku and price
        $this->_drawSku()->_drawPrice();

        if ($this->_helper->getConfigObject()->getDisplaySku() || $this->_helper->getConfigObject()->getDisplayPrice()) {
            $this->_addBottomPad();
        }

        // clearing floats
        $this->_clearFloats()->_drawHorizontalLine(false)->_addBottomPad();
        $this->_clearFloats()->_drawProductImage();
        $this->_drawProductDescription();
        // clearing floats

        if ($this->_helper->getConfigObject()->getDisplayDescriptionInSingleMode() || $this->_helper->getConfigObject()->getShowImageInSingleMode()) {
            $this->_clearFloats()->_drawHorizontalLine(false)->_addBottomPad();
        }

        $this->_drawProductAttributes();
    }


    /**
     * Draw product categories on the pdf page
     *
     * @return \Glugox\PDF\Model\Renderer\Product
     */
    protected function _drawCategories() {

        if ($this->_helper->getConfigObject()->getDisplayCategories()) {

            $page = $this->getPage();
            $fontSize = 12;
            $page->setFillColor(new \Zend_Pdf_Color_Html($this->_helper->getConfigObject()->getPdfColor(Config::COLOR_CATEGORIES)));
            $font = $this->_setFontRegular($fontSize);
            $product = $this->getProduct();

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
            $this->_currY -= $lineHeight;
            $page->drawText($categoriesBreadcrumb, $this->_currX, $this->_currY, 'UTF-8');

            $this->_addBottomPad();
        }

        return $this;
    }


    /**
     * Draw product name on pthe pdf page
     *
     * @return \Glugox\PDF\Model\Renderer\Product
     */
    protected function _drawTitle() {

        $product = $this->getProduct();
        $page = $this->getPage();
        $fontSize = $this->_helper->getConfigObject()->getPdfTitleFontSize();
        $maxChars = $this->_helper->getConfigObject()->getPdfTitleMaxCharsInLine();
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
            if ($index === 0) {
                $this->_titleY = $this->_currY;
            }
            $page->drawText(
                    trim(strip_tags($_value)), $this->_currX, $this->_currY, 'UTF-8'
            );
            $this->_titleBottomY = $this->_currY;
            ++$index;
        }

        $this->_addBottomPad();

        return $this;
    }


    /**
     * Draw product sku on the pdf page
     *
     * @return \Glugox\PDF\Model\Renderer\Product
     */
    protected function _drawSku() {

        if ($this->_helper->getConfigObject()->getDisplaySku()) {

            $page = $this->getPage();
            $fontSize = 12;
            $page->setFillColor(new \Zend_Pdf_Color_Html($this->_helper->getConfigObject()->getPdfColor(Config::COLOR_SKU)));
            $font = $this->_setFontRegular($fontSize);

            $this->_currY -= $this->getLineHeight($font, $fontSize);
            $page->drawText(__('SKU#') . ': ' . $this->getProduct()->getSku(), $this->_currX, $this->_currY, 'UTF-8');
        }

        return $this;
    }


    /**
     * Draw product price on the pdf page
     *
     * @return \Glugox\PDF\Model\Renderer\Product
     */
    protected function _drawPrice() {

        if ($this->_helper->getConfigObject()->getDisplayPrice()) {

            $preservedY = $this->_currY;
            $product = $this->getProduct();
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
            if ($priceInfoFinal) {
                $finalPrice = $priceInfoFinal->getValue();
                if ($finalPrice && $finalPrice < $regularPrice) {

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
                    //$this->_curritemMaxY = \min($regularPriceY, $this->_curritemMaxY);
                    $this->_currX += $estimatedWidth;
                    $regularPriceY += $lineHeight;
                }
            }

            /**
             * Drawing
             */
            $fontSize = 22;
            if (!$hasDiscount) {
                $fontColor = Config::COLOR_PRICE;
                $font = $this->_setFontBold($fontSize);
            } else {
                $fontColor = Config::COLOR_PRICE_OLD;
                $font = $this->_setFontRegular($fontSize);
                $regularPriceY += 0.5 * $lineHeight;
            }
            $page->setFillColor(new \Zend_Pdf_Color_Html($this->_helper->getConfigObject()->getPdfColor($fontColor)));
            $lineHeight = $this->getLineHeight($font, $fontSize);

            $estimatedWidth = $this->getPdf()->widthForStringUsingFontSize($regularPriceFormatted, $font, $fontSize);
            $this->_currX = $page->getWidth() - $estimatedWidth - $this->_bodyPadding;
            $page->drawText($regularPriceFormatted, $this->_currX, $regularPriceY, 'UTF-8');
            //$this->_curritemMaxY = \min($regularPriceY, $this->_curritemMaxY);
            $this->_currY = $regularPriceY;

            if ($hasDiscount) {
                $page->setLineColor(new \Zend_Pdf_Color_Html($this->_helper->getConfigObject()->getPdfColor(Config::COLOR_PRICE_OLD)));
                $page->drawLine($this->_currX, $regularPriceY + 0.4 * $lineHeight, $this->_currX + $estimatedWidth, $regularPriceY + 0.4 * $lineHeight);
            }

            $this->_currX += $estimatedWidth;
            $this->_currY = $preservedY;
        }

        return $this;
    }


    /**
     * Draws product image
     *
     * @return \Glugox\PDF\Model\Renderer\Product
     */
    protected function _drawProductImage() {


        if ($this->_helper->getConfigObject()->getShowImageInSingleMode()) {

            $product = $this->getProduct();
            $page = $this->getPage();
            $imagePath = $this->_helper->getProductImage($product);

            // define image resource
            $image = \Zend_Pdf_Image::imageWithPath($imagePath);
            $sizeFactor = $image->getPixelWidth() / $image->getPixelHeight();

            $maxWidth = $this->_helper->getConfigObject()->getSingleImageMaxWidth();
            $maxHeight = $this->_helper->getConfigObject()->getSingleImageMaxHeight();

            if ($maxWidth) {
                $imageWidth = $maxWidth;
                $imageHeight = $imageWidth / $sizeFactor;
                if ($maxHeight && $imageHeight > $maxHeight) {
                    $imageHeight = $maxHeight;
                    $imageWidth = $sizeFactor * $imageHeight;
                }
            } else if ($maxHeight) {
                $imageHeight = $maxHeight;
                $imageWidth = $sizeFactor * $imageHeight;
            } else {
                if ($sizeFactor > 1) {
                    $imageWidth = $this->_pageW;
                    $imageHeight = $imageWidth / $sizeFactor;
                } else {
                    $imageHeight = 320;
                    $imageWidth = $sizeFactor * $imageHeight;
                }
            }

            // write image to page
            $page->drawImage($image, $this->_currX, $this->_currY - $imageHeight, $this->_currX + $imageWidth, $this->_currY);
            $this->_currX += $imageWidth;
            $this->_currY -= $imageHeight;

            // one column layout
            $this->_currX = $this->_currXFixed;


            $this->_addBottomPad();
        }

        return $this;
    }


    /**
     * Draws product description
     *
     * @return \Glugox\PDF\Model\Renderer\Product
     */
    protected function _drawProductDescription() {

        if ($this->_helper->getConfigObject()->getDisplayDescriptionInSingleMode()) {

            $product = $this->getProduct();
            $page = $this->getPage();
            $fontSize = 14;
            $description = $product->getDescription();
            $description = $this->prepareTextForDrawing($description);

            if (!$description) {
                return $this;
            }

            $font = $this->_setFontRegular($fontSize);
            $page->setFillColor(new \Zend_Pdf_Color_Html($this->_helper->getConfigObject()->getPdfColor(Config::COLOR_TEXT)));


            $lineHeight = 2 * $this->getLineHeight($font, $fontSize);
            $this->_currY -= $lineHeight;

            $maxCharsPerLine = 96;
            $lines = $this->_string->split($description, $maxCharsPerLine, true, true);

            // valideate it does not exceed the page width
            $longLineValue = '';
            foreach ($lines as $_value) {
                if (\strlen($_value) > \strlen($longLineValue)) {
                    $longLineValue = $_value;
                }
            }
            if ($longLineValue) {
                $estimatedWidth = $this->getPdf()->widthForStringUsingFontSize($longLineValue, $font, $fontSize);
                if ($estimatedWidth > $this->_pageW) {
                    while ($estimatedWidth > $this->_pageW) {
                        $maxCharsPerLine -= 5;
                        $longLineValue = \substr($longLineValue, 0, $maxCharsPerLine);
                        $estimatedWidth = $this->getPdf()->widthForStringUsingFontSize($longLineValue, $font, $fontSize);
                    }

                    $lines = $this->_string->split($description, $maxCharsPerLine, true, true);
                }
            }

            foreach ($lines as $_value) {

                // check if we need to create new page, and do create it if needed
                $page = $this->_checkNewPage(2 * $lineHeight);
                if ($this->_newPageFlag) {
                    $this->_currY -= $lineHeight;
                    $page->setFillColor(new \Zend_Pdf_Color_Html($this->_helper->getConfigObject()->getPdfColor(Config::COLOR_TEXT)));
                }

                $page->drawText(
                        trim(strip_tags($_value)), $this->_currX, $this->_currY, 'UTF-8'
                );
                $this->_currY -= $lineHeight;
            }

            $this->_addBottomPad();
        }

        return $this;
    }


    /**
     * Draws product attributes
     *
     * @return \Glugox\PDF\Model\Renderer\Product
     */
    protected function _drawProductAttributes() {

        if ($this->_helper->getConfigObject()->getDisplayAttributesInSingleMode()) {

            $data = [];
            $page = $this->getPage();
            $excludeAttr = array();
            $product = $this->getProduct();
            $attributes = $product->getAttributes();

            // font style for first column (attribute labels)
            $fontSize = 14;
            $font = $this->_setFontBold($fontSize);
            $page->setFillColor(new \Zend_Pdf_Color_Html($this->_helper->getConfigObject()->getPdfColor(Config::COLOR_TEXT)));

            $this->_currY -= $this->_genPadding;
            // store current y position for using it in 2nd column
            $currY = $this->_currY;

            $estimatedHeight = 0;
            $lineHeight = $this->getLineHeight($font, $fontSize);

            foreach ($attributes as $attribute) {
                if ($attribute->getIsVisibleOnFront() && !in_array($attribute->getAttributeCode(), $excludeAttr)) {
                    $value = $attribute->getFrontend()->getValue($product);

                    if (!$product->hasData($attribute->getAttributeCode())) {
                        $value = __('N/A');
                    } elseif ((string) $value == '') {
                        $value = __('No');
                    } elseif ($attribute->getFrontendInput() == 'price' && is_string($value)) {
                        $value = $this->priceCurrency->convertAndFormat($value);
                    }

                    if (is_string($value) && strlen($value)) {
                        $data[$attribute->getAttributeCode()] = [
                            'label' => __($attribute->getStoreLabel()),
                            'value' => $value,
                            'code' => $attribute->getAttributeCode(),
                        ];

                        $estimatedHeight += 2 * $lineHeight;
                    }
                }
            }

            // check for new page before drawing attributes
            $page = $this->_checkNewPage($estimatedHeight);
            if ($this->_newPageFlag) {
                $this->_currY -= $lineHeight;
                $currY = $this->_currY;
                // we need to set styles for the new page
                $font = $this->_setFontBold($fontSize);
                $page->setFillColor(new \Zend_Pdf_Color_Html($this->_helper->getConfigObject()->getPdfColor(Config::COLOR_TEXT)));
            }


            if (\count($data)) {

                // draw attributes labels
                $font = $this->_setFontBold($fontSize);
                $attrLabelsMaxW = 0;
                foreach ($data as $code => $rAttribute) {

                    $attributeLine = $rAttribute['label'];
                    $attributeLine = $this->prepareTextForDrawing($attributeLine);
                    $estimatedWidth = $this->getPdf()->widthForStringUsingFontSize($attributeLine, $font, $fontSize);
                    $attrLabelsMaxW = \max($estimatedWidth, $attrLabelsMaxW);

                    $this->_currY -= $lineHeight;
                    $page->drawText(
                            trim(strip_tags($attributeLine)), $this->_currX, $this->_currY, 'UTF-8'
                    );
                    $this->_currY -= $lineHeight;
                }

                // draw attributes values
                $font = $this->_setFontRegular($fontSize);

                // second (values) column
                $this->_currX += ($attrLabelsMaxW + $this->_genPadding);
                $this->_currY = $currY;
                $attrValuesMaxW = 0;

                foreach ($data as $code => $rAttribute) {

                    $attributeLine = $rAttribute['value'];
                    $attributeLine = $this->prepareTextForDrawing($attributeLine);
                    $estimatedWidth = $this->getPdf()->widthForStringUsingFontSize($attributeLine, $font, $fontSize);
                    $attrValuesMaxW = \max($estimatedWidth, $attrValuesMaxW);

                    $this->_currY -= $lineHeight;
                    $page->drawText(
                            trim(strip_tags($attributeLine)), $this->_currX, $this->_currY, 'UTF-8'
                    );
                    $this->_currY -= $lineHeight;
                }

                $this->_currY += 2 * $lineHeight;
                $totalWidth = $attrLabelsMaxW + $this->_genPadding + $attrValuesMaxW;
            }
        }

        return $this;
    }


}
