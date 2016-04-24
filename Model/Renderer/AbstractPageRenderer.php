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
 * Page renderer that has functionality to render pdf page header
 * including logo, store name, header bottom line and some general methods
 * for drawing.
 *
 * @author Eko
 */
class AbstractPageRenderer extends \Glugox\PDF\Model\Renderer\AbstractItems {

    /**
     * @var type
     */
    protected $_currX;

    /**
     * @var type
     */
    protected $_currXFixed;

    /**
     * @var type
     */
    protected $_currY;

    /**
     * @var int
     */
    protected $_titleY = null;

    /**
     * @var int
     */
    protected $_titleBottomY = null;

    /**
     * @var type
     */
    protected $_pageW;

    /**
     * @var type
     */
    protected $_contentW;

    /**
     * @var type
     */
    protected $_genPadding;

    /**
     * @var type
     */
    protected $_bodyPadding;

    /**
     * @var type
     */
    protected $_pagePadding;

    /**
     * @var type
     */
    protected $_fontPaddingK = 0.2;

    /**
     * @var boolean Flag set only by _checkNewPage
     */
    protected $_newPageFlag = false;

    /**
     * Initializes page
     */
    protected function _initPage() {

        $page = $this->getPage();
        $w = $page->getWidth();
        $h = $page->getHeight();

        $this->_bodyPadding = $this->_helper->getConfigObject()->getPdfBodyPadding();
        $this->_pagePadding = $this->_helper->getConfigObject()->getPdfPagePadding();
        $this->_genPadding = $this->_helper->getConfigObject()->getPdfGeneralPadding();
        $this->_currXFixed = 0;
        $this->_currY = $page->getHeight() - $this->_bodyPadding;
        $this->_pageW = $w - 2 * $this->_helper->getConfigObject()->getPdfBodyPadding();
        $this->_contentW = $this->_pageW - 2 * $this->_helper->getConfigObject()->getPdfPagePadding();
        $this->_currX = $this->_currXFixed = $this->_bodyPadding;
    }


    /**
     * Initializes page
     *
     * @return \Zend_Pdf_Page
     */
    protected function _checkNewPage($nextObjectHeight = null) {

        $page = $this->getPage();
        $this->_newPageFlag = false;

        if (null !== $nextObjectHeight) {
            $this->_currY -= $nextObjectHeight;
        }

        if ($this->_currY < $this->_bodyPadding) {

            // preserve old page styles for new page
            $font = $page->getFont();
            $fontSize = $page->getFontSize();


            // new page
            $page = $this->getPdf()->newPage();
            $this->setPage($page);
            $this->_initPage();

            if ($this->_helper->getConfigObject()->getDrawHeaderOnEachPage()) {
                $this->_drawHeader();
            }

            $page->setFont($font, $fontSize);

            $this->_newPageFlag = true;
        }

        // revert currY value
        if (null !== $nextObjectHeight && !$this->_newPageFlag) {
            // if new page, no need to revert it , it is initialized to defeult position for new page
            $this->_currY += $nextObjectHeight;
        }

        return $page;
    }


    /**
     * Draw product to pdf
     */
    public function draw() {

        $this->_initPage();
        $this->_drawHeader();
    }


    /**
     * Adds the default bottom padding value of passed $padding
     * value to th current y position
     *
     * @param type $padding
     * @return \Glugox\PDF\Model\Renderer\Product
     */
    protected function _addBottomPad($padding = null) {

        $padding = null === $padding ? $this->_genPadding : $padding;
        $this->_currY -= $padding;

        return $this;
    }


    /**
     * Clear all floats. Setting the x position to fixed x
     *
     * @return \Glugox\PDF\Model\Renderer\Product
     */
    protected function _clearFloats() {

        $this->_currX = $this->_currXFixed;
        return $this;
    }


    /**
     * Draw pdf page header
     */
    protected function _drawHeader() {

        if ($this->_helper->getDisplayHeader()) {
            // floading logo and store name
            $this->_drawLogo()->_drawStoreName()->_addBottomPad($this->_bodyPadding);

            // clearing floats
            $this->_clearFloats()->_drawHorizontalLine(false)->_addBottomPad();
        }
    }


    /**
     * Draws a logo to the page header
     *
     * @return \Glugox\PDF\Model\Renderer\Product
     */
    protected function _drawLogo() {

        if ($this->_helper->getConfigObject()->getDisplayLogo()) {
            $page = $this->getPage();
            $imagePath = $this->_helper->getLogoImagePath();

            if (null !== $imagePath) {
                // define image resource
                $image = \Zend_Pdf_Image::imageWithPath($imagePath);
                $sizeFactor = $image->getPixelWidth() / $image->getPixelHeight();

                $imageHeight = $this->_helper->getConfigObject()->getLogoHeight();
                $imageWidth = $sizeFactor * $imageHeight;

                $this->_currY -= $imageHeight;
                // write image to page
                $page->drawImage($image, $this->_currX, $this->_currY, $this->_currX + $imageWidth, $this->_currY + $imageHeight);
            }
        }



        return $this;
    }


    /**
     * Draws the store name to the page header
     *
     * @return \Glugox\PDF\Model\Renderer\Product
     */
    protected function _drawStoreName() {

        if ($this->_helper->getConfigObject()->getDisplayStoreName()) {

            $storeName = $this->_helper->getStoreName();
            if (!empty($storeName)) {

                $page = $this->getPage();
                $fontSize = 12;
                $page->setFillColor(new \Zend_Pdf_Color_Html($this->_helper->getConfigObject()->getPdfColor(Config::COLOR_STORE_NAME)));
                $font = $this->_setFontRegular($fontSize);

                $estimatedWidth = $this->getPdf()->widthForStringUsingFontSize($storeName, $font, $fontSize);

                $this->_currX = $page->getWidth() - $estimatedWidth - $this->_bodyPadding;
                $page->drawText($storeName, $this->_currX, $this->_currY, 'UTF-8');
                $this->_currX += $estimatedWidth;
            }
        }

        return $this;
    }


    /**
     * Draw pdf page header line
     *
     * @param boolean $paddBottom
     * @return \Glugox\PDF\Model\Renderer\Product
     */
    protected function _drawHorizontalLine($paddBottom = true,
            $color = Config::COLOR_LINES) {

        $page = $this->getPage();

        // set line color
        $page->setLineColor(new \Zend_Pdf_Color_Html($this->_helper->getConfigObject()->getPdfColor($color)));
        $page->drawLine($this->_currX, $this->_currY, $this->_currX + $this->_pageW, $this->_currY);

        $this->_currY -= (int) $paddBottom * $this->_genPadding;

        return $this;
    }


    /**
     * Prepares string fro drawing into pdf
     *
     * @param type $str
     * @return type
     */
    protected function prepareTextForDrawing($str){
        return \html_entity_decode($str);
    }


}
